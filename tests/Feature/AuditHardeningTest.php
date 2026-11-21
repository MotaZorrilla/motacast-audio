<?php

namespace Tests\Feature;

use App\Enums\BookStatus;
use App\Jobs\ProcessBookJob;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\TrafficLog;
use App\Models\User;
use App\Services\AudioSynthesisService;
use App\Services\PdfExtractorService;
use App\Services\TtsTextNormalizerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class AuditHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_agent_spoofing_cannot_access_stream_retry_or_delete_private_books(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => 'user']);
        $attacker = User::factory()->create(['role' => 'user']);

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'Secreto Privado',
            'original_filename' => 'secreto.pdf',
            'pdf_path' => 'pdfs/secreto.pdf',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'ready',
        ]);

        Storage::disk('public')->put($book->pdf_path, '%PDF-1.4 SECRET');
        $audioPath = "audiobooks/{$book->id}/chapter_1.mp3";
        Storage::disk('public')->put($audioPath, 'SECRET_MP3_BYTES');

        $chapter = Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Capítulo Secreto',
            'content_text' => 'Contenido secreto del usuario.',
            'audio_path' => $audioPath,
            'status' => 'ready',
        ]);

        $crawlerHeaders = ['User-Agent' => 'WhatsApp/2.24.1 A'];

        // Unauthenticated spoofed crawler is blocked across all private endpoints
        $this->withHeaders($crawlerHeaders)->get(route('books.show', $book))->assertRedirect(route('login'));
        $this->withHeaders($crawlerHeaders)->get(route('books.pdf', $book))->assertRedirect(route('login'));
        $this->withHeaders($crawlerHeaders)->get(route('books.document.content', $book))->assertRedirect(route('login'));
        $this->withHeaders($crawlerHeaders)->get(route('books.transcription.download', $book))->assertRedirect(route('login'));
        $this->withHeaders($crawlerHeaders)->get(route('chapters.stream', $chapter))->assertRedirect(route('login'));
        $this->withHeaders($crawlerHeaders)->get(route('chapters.download', $chapter))->assertRedirect(route('login'));
        $this->withHeaders($crawlerHeaders)->post(route('books.retry', $book))->assertRedirect(route('login'));

        // Authenticated different user with spoofed User-Agent cannot read, retry, or delete
        $this->actingAs($attacker)->withHeaders($crawlerHeaders)
            ->delete(route('books.destroy', $book))
            ->assertStatus(403);

        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_record_web_traffic_stores_valid_device_type_string_and_ignores_status_polling(): void
    {
        $user = User::factory()->create();

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Mobile Safari/537.36',
        ])->get('/');

        $this->assertDatabaseHas('traffic_logs', [
            'path' => '/',
            'device_type' => 'mobile',
            'is_crawler' => false,
        ]);

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'Polling Ignore',
            'original_filename' => 'poll.pdf',
            'pdf_path' => 'pdfs/poll.pdf',
            'status' => 'extracting',
        ]);

        $countBefore = TrafficLog::count();
        $this->actingAs($user)->getJson(route('books.status', $book))->assertOk();
        $this->assertSame($countBefore, TrafficLog::count());
    }

    public function test_audio_streaming_returns_416_on_invalid_byte_range(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'Audio Range Test',
            'original_filename' => 'range.pdf',
            'pdf_path' => 'pdfs/range.pdf',
            'status' => 'ready',
        ]);

        $audioPath = "audiobooks/{$book->id}/chapter_1.mp3";
        Storage::disk('public')->put($audioPath, '0123456789');

        $chapter = Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Cap 1',
            'content_text' => 'Texto',
            'audio_path' => $audioPath,
            'status' => 'ready',
        ]);

        $this->actingAs($owner)
            ->withHeaders(['Range' => 'bytes=999-1500'])
            ->get(route('chapters.stream', $chapter))
            ->assertStatus(416);
    }

    public function test_process_book_job_marks_book_failed_when_all_tts_chapters_fail(): void
    {
        Storage::fake('public');

        $book = Book::create([
            'title' => 'Fallo TTS Total',
            'original_filename' => 'tts_fail.txt',
            'pdf_path' => 'pdfs/tts_fail.txt',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'transcription_only' => false,
            'status' => 'pending',
        ]);

        Storage::disk('public')->put($book->pdf_path, 'Contenido suficiente para probar fallo TTS.');

        $extractor = Mockery::mock(PdfExtractorService::class);
        $extractor->shouldReceive('extract')->once()->andReturn([
            'success' => true,
            'title' => 'Fallo TTS Total',
            'author' => 'Autor',
            'summary' => 'Resumen',
            'total_words' => 10,
            'chapters' => [
                [
                    'chapter_number' => 1,
                    'title' => 'Capítulo 1',
                    'text' => 'Texto de prueba para síntesis.',
                    'word_count' => 5,
                ],
            ],
        ]);

        $synthesizer = Mockery::mock(AudioSynthesisService::class);
        $synthesizer->shouldReceive('synthesize')
            ->andThrow(new \Exception('WSServerHandshakeError: 403'));

        $job = new ProcessBookJob($book->id);
        $job->handle($extractor, $synthesizer, new TtsTextNormalizerService);

        $book->refresh();
        $this->assertSame(BookStatus::Failed->value, $book->status);
        $this->assertStringContainsString('No se pudo sintetizar audio TTS', (string) $book->error_message);
    }
}
