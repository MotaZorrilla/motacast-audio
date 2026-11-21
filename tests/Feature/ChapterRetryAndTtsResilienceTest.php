<?php

namespace Tests\Feature;

use App\Enums\BookStatus;
use App\Enums\ChapterStatus;
use App\Jobs\SynthesizeChapterJob;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;
use App\Services\AudioSynthesisService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChapterRetryAndTtsResilienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_audio_synthesis_service_retries_transient_failure_and_succeeds(): void
    {
        config()->set('audiolibros.tts.max_attempts', 3);
        config()->set('audiolibros.tts.retry_delay_ms', 250);

        $service = new class extends AudioSynthesisService
        {
            public int $calls = 0;

            /** @var array<int, int> */
            public array $sleeps = [];

            protected function executeProcess(array $command): array
            {
                $this->calls++;

                if ($this->calls === 1) {
                    return [
                        'successful' => false,
                        'stdout' => json_encode(['success' => false, 'error' => 'Error en síntesis TTS: WS connection reset']),
                        'stderr' => '',
                        'exit_code' => 1,
                    ];
                }

                return [
                    'successful' => true,
                    'stdout' => json_encode(['success' => true, 'duration_seconds' => 42, 'file_size' => 12345]),
                    'stderr' => '',
                    'exit_code' => 0,
                ];
            }

            protected function sleepMilliseconds(int $milliseconds): void
            {
                $this->sleeps[] = $milliseconds;
            }
        };

        $outputPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'mz_retry_ok_'.uniqid().'.mp3';
        $result = $service->synthesize('Texto de prueba para síntesis neuronal.', $outputPath);

        $this->assertTrue($result['success']);
        $this->assertSame(42, $result['duration_seconds']);
        $this->assertSame(2, $service->calls);
        $this->assertSame([250], $service->sleeps);
    }

    public function test_audio_synthesis_service_captures_stdout_json_error_when_stderr_is_empty(): void
    {
        config()->set('audiolibros.tts.max_attempts', 2);
        config()->set('audiolibros.tts.retry_delay_ms', 100);

        $service = new class extends AudioSynthesisService
        {
            public int $calls = 0;

            /** @var array<int, int> */
            public array $sleeps = [];

            protected function executeProcess(array $command): array
            {
                $this->calls++;

                return [
                    'successful' => false,
                    'stdout' => json_encode(['success' => false, 'error' => 'Error en síntesis TTS: 403 Invalid response status']),
                    'stderr' => '',
                    'exit_code' => 1,
                ];
            }

            protected function sleepMilliseconds(int $milliseconds): void
            {
                $this->sleeps[] = $milliseconds;
            }
        };

        $outputPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'mz_retry_fail_'.uniqid().'.mp3';

        try {
            $service->synthesize('Texto que falla repetidamente.', $outputPath);
            $this->fail('Se esperaba una excepción tras agotar los intentos de síntesis.');
        } catch (Exception $e) {
            $this->assertStringContainsString('403 Invalid response status', $e->getMessage());
            $this->assertSame(2, $service->calls);
            $this->assertSame([100], $service->sleeps);
        }
    }

    public function test_audio_synthesis_service_reports_exit_code_when_stdout_and_stderr_are_empty(): void
    {
        config()->set('audiolibros.tts.max_attempts', 1);

        $service = new class extends AudioSynthesisService
        {
            protected function executeProcess(array $command): array
            {
                return [
                    'successful' => false,
                    'stdout' => '',
                    'stderr' => '',
                    'exit_code' => 137,
                ];
            }
        };

        $outputPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'mz_retry_oom_'.uniqid().'.mp3';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('código 137');
        $service->synthesize('Texto con salida vacía.', $outputPath);
    }

    public function test_synthesize_chapter_job_recovers_failed_chapter_and_updates_book_totals(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'Libro con Capítulo Fallido',
            'original_filename' => 'libro.pdf',
            'pdf_path' => 'pdfs/libro.pdf',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => BookStatus::Ready->value,
            'total_chapters' => 2,
            'processed_chapters' => 1,
            'total_duration' => 300,
        ]);

        Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Capítulo 1',
            'content_text' => 'Contenido listo.',
            'word_count' => 100,
            'audio_path' => "audiobooks/{$book->id}/chapter_1.mp3",
            'duration_seconds' => 300,
            'status' => ChapterStatus::Ready->value,
        ]);

        $failedChapter = Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 2,
            'title' => 'Capítulo 2',
            'content_text' => 'Contenido pendiente de reintento.',
            'word_count' => 120,
            'duration_seconds' => 0,
            'status' => ChapterStatus::Failed->value,
            'error_message' => 'Error en proceso TTS: timeout',
        ]);

        $mockSynth = $this->mock(AudioSynthesisService::class);
        $mockSynth->shouldReceive('synthesize')
            ->once()
            ->andReturn(['success' => true, 'duration_seconds' => 180]);

        (new SynthesizeChapterJob($failedChapter->id))->handle($mockSynth);

        $failedChapter->refresh();
        $book->refresh();

        $this->assertSame(ChapterStatus::Ready->value, $failedChapter->status);
        $this->assertSame(180, $failedChapter->duration_seconds);
        $this->assertNull($failedChapter->error_message);
        $this->assertSame("audiobooks/{$book->id}/chapter_2.mp3", $failedChapter->audio_path);
        $this->assertSame(2, $book->processed_chapters);
        $this->assertSame(480, $book->total_duration);
        $this->assertSame(BookStatus::Ready->value, $book->status);
    }

    public function test_owner_sees_failed_chapter_retry_button_and_can_dispatch_retry(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'Libro Con Error Parcial',
            'original_filename' => 'parcial.pdf',
            'pdf_path' => 'pdfs/parcial.pdf',
            'voice' => 'es-VE-SebastianNeural',
            'status' => BookStatus::Ready->value,
            'total_chapters' => 1,
            'processed_chapters' => 0,
        ]);

        $chapter = Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 5,
            'title' => 'Capítulo V: Discusión',
            'content_text' => 'Texto de discusión.',
            'word_count' => 200,
            'status' => ChapterStatus::Failed->value,
            'error_message' => 'Error en proceso TTS: Corte temporal de red',
        ]);

        $showResponse = $this->actingAs($user)->get(route('books.show', $book));
        $showResponse->assertOk();
        $showResponse->assertSee('Error TTS');
        $showResponse->assertSee('Reintentar');
        $showResponse->assertSee('Corte temporal de red');

        $retryResponse = $this->actingAs($user)->post(route('chapters.retry', $chapter));
        $retryResponse->assertRedirect(route('books.show', $book));
        $retryResponse->assertSessionHas('info');

        $chapter->refresh();
        $this->assertSame(ChapterStatus::Pending->value, $chapter->status);
        $this->assertNull($chapter->error_message);

        Queue::assertPushed(SynthesizeChapterJob::class, fn (SynthesizeChapterJob $job) => $job->chapterId === $chapter->id);
    }

    public function test_other_user_cannot_retry_private_chapter_and_ready_chapter_is_not_requeued(): void
    {
        Queue::fake();

        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'Libro Privado',
            'original_filename' => 'privado.pdf',
            'pdf_path' => 'pdfs/privado.pdf',
            'voice' => 'es-VE-SebastianNeural',
            'status' => BookStatus::Ready->value,
            'total_chapters' => 2,
            'processed_chapters' => 1,
        ]);

        $failedChapter = Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Fallido',
            'content_text' => 'Texto fallido.',
            'word_count' => 50,
            'status' => ChapterStatus::Failed->value,
        ]);

        $readyChapter = Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 2,
            'title' => 'Listo',
            'content_text' => 'Texto listo.',
            'word_count' => 50,
            'audio_path' => "audiobooks/{$book->id}/chapter_2.mp3",
            'duration_seconds' => 60,
            'status' => ChapterStatus::Ready->value,
        ]);

        // Intruder receives 403 Forbidden
        $this->actingAs($intruder)
            ->post(route('chapters.retry', $failedChapter))
            ->assertForbidden();

        // Guest receives redirect to login
        auth()->logout();
        $this->post(route('chapters.retry', $failedChapter))
            ->assertRedirect(route('login'));

        // Owner trying to retry an already ready chapter does NOT queue a job
        $this->actingAs($owner)
            ->post(route('chapters.retry', $readyChapter))
            ->assertRedirect(route('books.show', $book));

        Queue::assertNothingPushed();
    }
}
