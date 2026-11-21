<?php

namespace Tests\Feature;

use App\Jobs\ProcessBookJob;
use App\Models\Book;
use App\Models\User;
use App\Services\PdfExtractorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class AdminUnlimitedUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_view_shows_unlimited_badge_for_admin_and_100mb_for_standard_user(): void
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'book_limit' => -1]);

        $this->actingAs($user)
            ->get(route('books.create'))
            ->assertOk()
            ->assertSee('hasta 100 MB')
            ->assertDontSee('sin límite de tamaño (Admin)');

        $this->actingAs($admin)
            ->get(route('books.create'))
            ->assertOk()
            ->assertSee('sin límite de tamaño (Admin)');
    }

    public function test_standard_user_is_still_blocked_above_100mb_on_store_and_stt_preview(): void
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active', 'book_limit' => 5]);
        $oversizedAudio = UploadedFile::fake()->create('heavy.mp3', 110000, 'audio/mpeg'); // ~107 MB

        $this->actingAs($user)
            ->post(route('books.store'), [
                'pdf_file' => $oversizedAudio,
                'voice' => 'es-VE-SebastianNeural',
                'speed_rate' => '+0%',
                'pitch' => '+0Hz',
            ])
            ->assertSessionHasErrors('pdf_file');

        $this->actingAs($user)
            ->postJson(route('books.stt.preview'), [
                'audio' => $oversizedAudio,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['audio']);
    }

    public function test_non_admin_cannot_access_chunked_upload_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $chunk = UploadedFile::fake()->create('part0.bin', 100);

        $this->actingAs($user)
            ->postJson(route('uploads.chunk'), [
                'upload_id' => (string) Str::uuid(),
                'chunk_index' => 0,
                'total_chunks' => 1,
                'filename' => 'audio.mp3',
                'chunk' => $chunk,
            ])
            ->assertStatus(403);
    }

    public function test_admin_bypasses_100mb_limit_on_direct_store_and_stt_preview(): void
    {
        Queue::fake();
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'book_limit' => -1]);
        $oversizedAudio = UploadedFile::fake()->create('conferencia-larga.mp3', 150000, 'audio/mpeg'); // ~146 MB

        $storeResponse = $this->actingAs($admin)->post(route('books.store'), [
            'pdf_file' => $oversizedAudio,
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $book = Book::latest('id')->first();
        $this->assertNotNull($book);
        $storeResponse->assertRedirect(route('books.show', $book->id));
        Queue::assertPushed(ProcessBookJob::class);

        $mockExtractor = Mockery::mock(PdfExtractorService::class);
        $mockExtractor->shouldReceive('extract')
            ->once()
            ->andReturn([
                'success' => true,
                'title' => 'Conferencia Larga',
                'author' => 'Transcripción de Audio (STT)',
                'summary' => 'Texto transcrito sin límite.',
                'total_words' => 120,
                'chapters' => [
                    ['chapter_number' => 1, 'title' => 'Parte 1', 'text' => 'Contenido de prueba largo.', 'word_count' => 4],
                ],
            ]);
        $this->app->instance(PdfExtractorService::class, $mockExtractor);

        $this->actingAs($admin)
            ->postJson(route('books.stt.preview'), [
                'audio' => $oversizedAudio,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'title' => 'Conferencia Larga',
            ]);
    }

    public function test_admin_can_upload_chunks_and_complete_store_and_stt_preview(): void
    {
        Queue::fake();
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'book_limit' => -1]);
        $uploadId = (string) Str::uuid();

        $chunk0 = UploadedFile::fake()->createWithContent('part0', 'CHUNK_ZERO_BYTES_');
        $chunk1 = UploadedFile::fake()->createWithContent('part1', 'CHUNK_ONE_BYTES');

        $this->actingAs($admin)
            ->postJson(route('uploads.chunk'), [
                'upload_id' => $uploadId,
                'chunk_index' => 0,
                'total_chunks' => 2,
                'filename' => 'seminario-completo.mp3',
                'chunk' => $chunk0,
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'completed' => false]);

        $this->actingAs($admin)
            ->postJson(route('uploads.chunk'), [
                'upload_id' => $uploadId,
                'chunk_index' => 1,
                'total_chunks' => 2,
                'filename' => 'seminario-completo.mp3',
                'chunk' => $chunk1,
            ])
            ->assertOk()
            ->assertJson(['success' => true, 'completed' => true, 'filename' => 'seminario-completo.mp3']);

        $storeResponse = $this->actingAs($admin)->post(route('books.store'), [
            'chunked_upload_id' => $uploadId,
            'chunked_filename' => 'seminario-completo.mp3',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $book = Book::latest('id')->first();
        $this->assertNotNull($book);
        $this->assertEquals('seminario-completo', $book->title);
        $storeResponse->assertRedirect(route('books.show', $book->id));
        Storage::disk('public')->assertExists($book->pdf_path);
        $this->assertSame('CHUNK_ZERO_BYTES_CHUNK_ONE_BYTES', Storage::disk('public')->get($book->pdf_path));
        $this->assertFileDoesNotExist(storage_path('app/private/chunks/'.$uploadId.'.meta.json'));

        // Now test chunked STT preview cleanup & transcription
        $sttUploadId = (string) Str::uuid();
        $sttChunk = UploadedFile::fake()->createWithContent('part0', 'AUDIO_STT_BYTES');

        $sttRes = $this->actingAs($admin)
            ->postJson(route('uploads.chunk'), [
                'upload_id' => $sttUploadId,
                'chunk_index' => 0,
                'total_chunks' => 1,
                'filename' => 'nota-larga.opus',
                'chunk' => $sttChunk,
            ]);

        $this->assertSame(200, $sttRes->status(), $sttRes->getContent());
        $sttRes->assertJson(['completed' => true]);

        $mockExtractor = Mockery::mock(PdfExtractorService::class);
        $mockExtractor->shouldReceive('extract')
            ->once()
            ->andReturn([
                'success' => true,
                'title' => 'Nota Larga',
                'author' => 'Transcripción de Audio (STT)',
                'summary' => 'Resumen de nota larga.',
                'total_words' => 8,
                'chapters' => [
                    ['chapter_number' => 1, 'title' => 'Parte 1', 'text' => 'Texto transcrito desde carga fragmentada.', 'word_count' => 6],
                ],
            ]);
        $this->app->instance(PdfExtractorService::class, $mockExtractor);

        $this->actingAs($admin)
            ->postJson(route('books.stt.preview'), [
                'chunked_upload_id' => $sttUploadId,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'title' => 'Nota Larga',
                'text' => 'Texto transcrito desde carga fragmentada.',
            ]);

        $this->assertFileDoesNotExist(storage_path('app/private/chunks/'.$sttUploadId.'.meta.json'));
    }
}
