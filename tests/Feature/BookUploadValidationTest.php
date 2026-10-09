<?php

namespace Tests\Feature;

use App\Jobs\ProcessBookJob;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_validation_accepts_docx_file(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('document.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($user)->post('/books', [
            'pdf_file' => $file,
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_upload_validation_accepts_txt_file(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('notes.txt', 50, 'text/plain');

        $response = $this->actingAs($user)->post('/books', [
            'pdf_file' => $file,
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_upload_validation_rejects_unsupported_formats(): void
    {
        $user = User::factory()->create();
        $exeFile = UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream');

        $response = $this->actingAs($user)->post('/books', [
            'pdf_file' => $exeFile,
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response->assertSessionHasErrors(['pdf_file']);
    }

    public function test_upload_validation_accepts_markdown_file(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('readme.md', 30, 'text/markdown');

        $response = $this->actingAs($user)->post('/books', [
            'pdf_file' => $file,
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_stream_summary_audio_returns_stream_response(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $audioPath = 'audiobooks/test_summary.mp3';
        Storage::disk('public')->put($audioPath, str_repeat('A', 1024));

        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'Libro con Resumen',
            'original_filename' => 'doc.docx',
            'pdf_path' => 'pdfs/doc.docx',
            'summary' => 'Este es un resumen ejecutivo de prueba para el documento docx.',
            'summary_audio_path' => $audioPath,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($user)->get(route('books.summary.stream', $book->id));
        $response->assertStatus(200);
        $this->assertEquals('audio/mpeg', $response->headers->get('Content-Type'));
    }

    public function test_book_persists_summary_and_summary_audio_path(): void
    {
        $user = User::factory()->create();
        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'Prueba de Resumen NLP',
            'original_filename' => 'ensayo.md',
            'pdf_path' => 'pdfs/ensayo.md',
            'summary' => 'Este es el resumen generado automáticamente por el motor NLP.',
            'summary_audio_path' => 'audiobooks/1/summary.mp3',
            'status' => 'ready',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'summary' => 'Este es el resumen generado automáticamente por el motor NLP.',
            'summary_audio_path' => 'audiobooks/1/summary.mp3',
        ]);

        $this->assertEquals('Este es el resumen generado automáticamente por el motor NLP.', $book->fresh()->summary);
    }

    public function test_upload_with_direct_raw_text_creates_book_and_dispatches_job(): void
    {
        Queue::fake();
        Storage::fake('public');
        $user = User::factory()->create();

        $sampleText = 'Este es un texto redactado directamente en la plataforma para su conversión rápida a voz neuronal. Contiene varias oraciones para cumplir con la longitud mínima requerida y validar el flujo integral.';

        $response = $this->actingAs($user)->post(route('books.store'), [
            'raw_text' => $sampleText,
            'title' => 'Artículo de Prueba Directo',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('books', [
            'title' => 'Artículo de Prueba Directo',
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        Queue::assertPushed(ProcessBookJob::class);
    }

    public function test_upload_validation_rejects_when_both_file_and_raw_text_are_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response->assertSessionHasErrors(['pdf_file', 'raw_text']);
    }

    public function test_document_content_api_returns_json_with_chapters_and_format(): void
    {
        $user = User::factory()->create();
        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'Manual de Microservicios',
            'original_filename' => 'manual.docx',
            'pdf_path' => 'pdfs/manual.docx',
            'summary' => 'Resumen ejecutivo de prueba.',
            'status' => 'ready',
        ]);

        $ch1 = Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Introducción a Cloud',
            'content_text' => 'Contenido completo del capítulo uno.',
            'duration_seconds' => 120,
            'word_count' => 80,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($user)->get(route('books.document.content', $book->id));
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $book->id,
            'title' => 'Manual de Microservicios',
            'file_type' => 'docx',
            'total_chapters' => 1,
            'chapters' => [
                [
                    'id' => $ch1->id,
                    'chapter_number' => 1,
                    'title' => 'Introducción a Cloud',
                    'content_text' => 'Contenido completo del capítulo uno.',
                ],
            ],
        ]);
    }
}
