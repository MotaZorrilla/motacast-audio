<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;
use App\Services\PdfExtractorService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class AudioTranscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_stt_preview_requires_audio_file(): void
    {
        $response = $this->postJson(route('books.stt.preview'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['audio']);
    }

    public function test_stt_preview_rejects_non_audio_file(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson(route('books.stt.preview'), [
            'audio' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['audio']);
    }

    public function test_stt_preview_accepts_valid_audio_file_and_returns_transcription(): void
    {
        $file = UploadedFile::fake()->create('recording.mp3', 200, 'audio/mpeg');

        $mockExtractor = Mockery::mock(PdfExtractorService::class);
        $mockExtractor->shouldReceive('extract')
            ->once()
            ->andReturn([
                'success' => true,
                'title' => 'Entrevista Grabada',
                'author' => 'Transcripción de Audio (STT)',
                'summary' => 'Resumen de la entrevista grabada.',
                'total_words' => 45,
                'chapters' => [
                    [
                        'chapter_number' => 1,
                        'title' => 'Parte 1',
                        'text' => 'Buenos días, bienvenidos a esta sesión de trabajo sobre ingeniería de software.',
                        'word_count' => 12,
                    ],
                    [
                        'chapter_number' => 2,
                        'title' => 'Parte 2',
                        'text' => 'Hoy abordaremos la importancia del testing unitario y la integración continua.',
                        'word_count' => 11,
                    ],
                ],
            ]);
        $this->app->instance(PdfExtractorService::class, $mockExtractor);

        $response = $this->postJson(route('books.stt.preview'), [
            'audio' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'title' => 'Entrevista Grabada',
            'words' => 45,
            'chapters_count' => 2,
        ]);
        $this->assertStringContainsString('Buenos días, bienvenidos a esta sesión de trabajo', $response->json('text'));
    }

    public function test_stt_preview_handles_unhappy_path_when_no_speech_detected(): void
    {
        $file = UploadedFile::fake()->create('silent.wav', 100, 'audio/wav');

        $mockExtractor = Mockery::mock(PdfExtractorService::class);
        $mockExtractor->shouldReceive('extract')
            ->once()
            ->andThrow(new Exception('No se detectó voz o habla comprensible en el archivo de audio.'));
        $this->app->instance(PdfExtractorService::class, $mockExtractor);

        $response = $this->postJson(route('books.stt.preview'), [
            'audio' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'No se detectó voz o habla comprensible en el archivo.',
        ]);
    }

    public function test_download_transcription_returns_txt_stream(): void
    {
        $user = User::factory()->create();
        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'Conferencia Magistral',
            'author' => 'Héctor Mota',
            'summary' => 'Resumen ejecutivo de la conferencia.',
            'original_filename' => 'audio.mp3',
            'pdf_path' => 'audiobooks/test.mp3',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'ready',
        ]);

        Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Introducción',
            'content_text' => 'Texto completo de la introducción transcrita.',
            'word_count' => 10,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($user)->get(route('books.transcription.download', [
            'book' => $book->id,
            'format' => 'txt',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/plain; charset=UTF-8');
        $this->assertStringContainsString('Conferencia Magistral', $response->streamedContent());
        $this->assertStringContainsString('Héctor Mota', $response->streamedContent());
        $this->assertStringContainsString('Texto completo de la introducción transcrita.', $response->streamedContent());
    }

    public function test_download_transcription_returns_markdown_stream(): void
    {
        $user = User::factory()->create();
        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'Notas de Audio',
            'author' => 'René Mota',
            'original_filename' => 'audio.mp3',
            'pdf_path' => 'audiobooks/test.mp3',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'ready',
        ]);

        Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Punto 1',
            'content_text' => 'Contenido en markdown de las notas.',
            'word_count' => 10,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($user)->get(route('books.transcription.download', [
            'book' => $book->id,
            'format' => 'md',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/markdown; charset=UTF-8');
        $this->assertStringContainsString('# Notas de Audio', $response->streamedContent());
        $this->assertStringContainsString('## Punto 1', $response->streamedContent());
    }

    public function test_unauthorized_user_cannot_download_other_user_transcription(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'Audio Privado',
            'original_filename' => 'audio.mp3',
            'pdf_path' => 'audiobooks/test.mp3',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($otherUser)->get(route('books.transcription.download', [
            'book' => $book->id,
            'format' => 'txt',
        ]));

        $response->assertStatus(403);
    }

    public function test_guest_can_download_their_own_trial_transcription(): void
    {
        $book = Book::create([
            'user_id' => null,
            'title' => 'Dictado de Prueba',
            'original_filename' => 'audio.mp3',
            'pdf_path' => 'audiobooks/test.mp3',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'ready',
        ]);

        Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Dictado de Prueba',
            'content_text' => 'Texto transcrito para invitado.',
            'word_count' => 10,
            'status' => 'ready',
        ]);

        $response = $this->withSession(['guest_book_id' => $book->id])
            ->get(route('books.transcription.download', [
                'book' => $book->id,
                'format' => 'txt',
            ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('Texto transcrito para invitado.', $response->streamedContent());
    }

    public function test_create_view_contains_audio_to_text_stt_elements(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('books.create'));

        $response->assertStatus(200);
        $response->assertSee('sttAudioInput', false);
        $response->assertSee('sttStatusBox', false);
        $response->assertSee('Audio a Texto (STT)', false);
        $response->assertSee('stepVoiceSection', false);
        $response->assertSee('stepAudioSection', false);
        $response->assertSee('Opciones de Archivo de Audio y Transcripción', false);
    }

    public function test_create_view_contains_updated_onboarding_guide_and_steps(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('books.create'));

        $response->assertStatus(200);
        $response->assertSee('¿Cómo usar MotaCastAudio? — Fácil en 3 Pasos', false);
        $response->assertSee('Elige tu archivo o audio', false);
        $response->assertSee('Configura según el formato', false);
        $response->assertSee('Escucha y exporta al instante', false);
    }

    public function test_app_version_displays_v0_9_5_beta(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertSee('v0.9.5-beta', false);
    }

    public function test_stt_preview_accepts_video_files_and_returns_transcription(): void
    {
        $file = UploadedFile::fake()->create('lecture.mp4', 500, 'video/mp4');

        $mockExtractor = Mockery::mock(PdfExtractorService::class);
        $mockExtractor->shouldReceive('extract')
            ->once()
            ->andReturn([
                'success' => true,
                'title' => 'Video Clase Magistral',
                'author' => 'Transcripción de Video (STT)',
                'summary' => 'Resumen de la clase extraída de video.',
                'total_words' => 30,
                'chapters' => [
                    [
                        'chapter_number' => 1,
                        'title' => 'Introducción del Video',
                        'text' => 'En este tutorial aprenderemos a automatizar despliegues con Docker.',
                        'word_count' => 10,
                    ],
                ],
            ]);
        $this->app->instance(PdfExtractorService::class, $mockExtractor);

        $response = $this->postJson(route('books.stt.preview'), [
            'audio' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'title' => 'Video Clase Magistral',
            'words' => 30,
        ]);
        $this->assertStringContainsString('En este tutorial aprenderemos', $response->json('text'));
    }

    public function test_create_view_contains_media_strategy_modal_and_retention_options(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('books.create'));

        $response->assertStatus(200);
        $response->assertSee('motaMediaStrategyModal', false);
        $response->assertSee('keep_original_media', false);
        $response->assertSee('keepOriginalMediaHidden', false);
        $response->assertSee('Desechar el archivo original tras transcribir', false);
        $response->assertSee('Conservar archivo multimedia original en el servidor', false);
        $response->assertSee('VIDEO (MP4, MKV)', false);
    }

    public function test_store_book_persists_keep_original_media_flag(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('podcast.mp3', 200, 'audio/mpeg');

        Queue::fake();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'pdf_file' => $file,
            'title' => 'Podcast Episodio 1',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'keep_original_media' => '1',
        ]);

        $response->assertRedirect();
        $book = Book::where('title', 'Podcast Episodio 1')->first();
        $this->assertNotNull($book);
        $this->assertTrue($book->keep_original_media);
    }

    public function test_show_view_contains_transcription_action_buttons(): void
    {
        $user = User::factory()->create();
        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'Libro Con Transcripción',
            'original_filename' => 'audio.mp3',
            'pdf_path' => 'audiobooks/test.mp3',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($user)->get(route('books.show', $book->id));

        $response->assertStatus(200);
        $response->assertSee('Texto (.TXT)', false);
        $response->assertSee(route('books.transcription.download', ['book' => $book->id, 'format' => 'txt']), false);
    }
}
