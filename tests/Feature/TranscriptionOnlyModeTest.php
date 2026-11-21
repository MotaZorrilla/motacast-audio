<?php

namespace Tests\Feature;

use App\Enums\BookStatus;
use App\Enums\ChapterStatus;
use App\Jobs\ProcessBookJob;
use App\Models\Book;
use App\Models\User;
use App\Services\AudioSynthesisService;
use App\Services\PdfExtractorService;
use App\Services\TtsTextNormalizerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class TranscriptionOnlyModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_book_supports_transcription_only_without_voice_fields(): void
    {
        Queue::fake();
        Storage::fake('public');

        $admin = User::factory()->create([
            'email' => 'hector@motazorrilla.com',
            'role' => 'admin',
            'status' => 'active',
            'book_limit' => -1,
        ]);

        $audio = UploadedFile::fake()->create('reunion.m4a', 512, 'audio/mp4');

        $response = $this->actingAs($admin)->post(route('books.store'), [
            'pdf_file' => $audio,
            'transcription_only' => '1',
            'keep_original_media' => '0',
        ]);

        $book = Book::latest('id')->first();
        $this->assertNotNull($book);
        $this->assertTrue($book->transcription_only);
        $this->assertFalse($book->keep_original_media);
        $response->assertRedirect(route('books.show', $book->id));
        Queue::assertPushed(ProcessBookJob::class);
    }

    public function test_process_book_job_skips_tts_and_deletes_temp_audio_in_transcription_only_mode(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'email' => 'hector@motazorrilla.com',
            'role' => 'admin',
            'status' => 'active',
            'book_limit' => -1,
        ]);

        Storage::disk('public')->put('pdfs/conversa.m4a', 'FAKE_M4A_BYTES');

        $book = Book::create([
            'user_id' => $admin->id,
            'title' => 'Conversación Grabada',
            'original_filename' => 'conversa.m4a',
            'pdf_path' => 'pdfs/conversa.m4a',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'keep_original_media' => false,
            'transcription_only' => true,
            'status' => BookStatus::Pending->value,
        ]);

        $mockExtractor = Mockery::mock(PdfExtractorService::class);
        $mockExtractor->shouldReceive('extract')
            ->once()
            ->andReturn([
                'success' => true,
                'title' => 'Conversación Grabada',
                'author' => 'Transcripción de Audio (STT)',
                'summary' => 'Resumen automático de la conversación.',
                'total_words' => 24,
                'chapters' => [
                    [
                        'chapter_number' => 1,
                        'title' => 'Parte 1',
                        'text' => 'Primer bloque de texto transcrito correctamente.',
                        'word_count' => 7,
                    ],
                    [
                        'chapter_number' => 2,
                        'title' => 'Parte 2',
                        'text' => 'Segundo bloque de texto transcrito para el administrador.',
                        'word_count' => 8,
                    ],
                ],
            ]);

        $mockSynthesizer = Mockery::mock(AudioSynthesisService::class);
        $mockSynthesizer->shouldNotReceive('synthesize');

        $job = new ProcessBookJob($book->id);
        $job->handle($mockExtractor, $mockSynthesizer, new TtsTextNormalizerService);

        $book->refresh();
        $this->assertEquals(BookStatus::Ready->value, $book->status);
        $this->assertEquals(2, $book->processed_chapters);
        $this->assertEquals(0, $book->total_duration);
        $this->assertNull($book->summary_audio_path);

        foreach ($book->chapters as $ch) {
            $this->assertEquals(ChapterStatus::Ready->value, $ch->status);
            $this->assertNull($ch->audio_path);
            $this->assertEquals(0, $ch->duration_seconds);
        }

        Storage::disk('public')->assertMissing('pdfs/conversa.m4a');
    }

    public function test_show_and_index_views_render_transcription_only_mode_cleanly(): void
    {
        $user = User::factory()->create();
        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'Reunión Larga STT',
            'original_filename' => 'reunion.m4a',
            'pdf_path' => 'pdfs/reunion.m4a',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'transcription_only' => true,
            'status' => BookStatus::Ready->value,
            'total_chapters' => 1,
            'processed_chapters' => 1,
            'total_words' => 12,
        ]);

        $book->chapters()->create([
            'chapter_number' => 1,
            'title' => 'Bloque 1',
            'content_text' => 'Contenido de reunión transcrita sin audio TTS.',
            'word_count' => 7,
            'status' => ChapterStatus::Ready->value,
        ]);

        $this->actingAs($user)
            ->get(route('books.index'))
            ->assertOk()
            ->assertSee('Solo Transcripción')
            ->assertSee('Leer Transcripción');

        $this->actingAs($user)
            ->get(route('books.show', $book->id))
            ->assertOk()
            ->assertSee('Transcripción Lista')
            ->assertSee('Secciones de la Transcripción')
            ->assertSee('Copiar Todo')
            ->assertSee('Markdown (.MD)')
            ->assertDontSee('id="stickyPlayerBar"', false);
    }

    public function test_admin_seeder_sets_hector_email_and_password(): void
    {
        $this->artisan('admin:set-password')->assertSuccessful();

        $admin = User::where('email', 'hector@motazorrilla.com')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('Baralo00.', $admin->password));
    }
}
