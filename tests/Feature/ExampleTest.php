<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Chapter;
use App\Jobs\ProcessBookJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_time_guest_accesses_trial_upload_at_root(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Agregar Documento o Libro');
    }

    public function test_returning_guest_with_used_trial_is_redirected_to_login(): void
    {
        $response = $this->withSession(['guest_upload_count' => 1])->get('/');
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_when_accessing_protected_books(): void
    {
        $response = $this->get('/books');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_accesses_home(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Mis Documentos');
    }

    public function test_guest_with_active_trial_book_is_redirected_to_book_show_from_home(): void
    {
        $book = \App\Models\Book::create([
            'user_id' => null,
            'title' => 'Libro Invitado Activo',
            'original_filename' => 'invitado.pdf',
            'pdf_path' => 'pdfs/invitado.pdf',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'ready',
        ]);

        $response = $this->withSession(['guest_book_id' => $book->id, 'guest_upload_count' => 1])->get('/');
        $response->assertRedirect(route('books.show', $book->id));

        $showResponse = $this->withSession(['guest_book_id' => $book->id])->get(route('books.show', $book->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Libro Invitado Activo');
        $showResponse->assertSee('Libro de Prueba');
    }

    public function test_register_page_displays_return_to_trial_book_link_when_session_present(): void
    {
        $book = \App\Models\Book::create([
            'user_id' => null,
            'title' => 'Libro Invitado Para Salir',
            'original_filename' => 'invitado2.pdf',
            'pdf_path' => 'pdfs/invitado2.pdf',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'ready',
        ]);

        $response = $this->withSession(['guest_book_id' => $book->id])->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('Volver a mi Audiolibro de Prueba');
    }

    public function test_guest_can_access_their_book_even_if_user_id_is_assigned(): void
    {
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $book = \App\Models\Book::create([
            'user_id' => $admin->id,
            'title' => 'Libro Asignado a Admin',
            'original_filename' => 'assigned.pdf',
            'pdf_path' => 'pdfs/assigned.pdf',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'ready',
        ]);

        // As guest with matching session token, access is granted
        $response = $this->withSession(['guest_book_id' => $book->id])->get(route('books.show', $book->id));
        $response->assertStatus(200);
        $response->assertSee('Libro Asignado a Admin');
    }

    public function test_guest_with_stale_or_missing_book_id_cleans_session_and_accesses_onboarding(): void
    {
        // Session points to non-existent book ID 99999
        $response = $this->withSession(['guest_book_id' => 99999, 'guest_upload_count' => 1])->get('/');
        $response->assertStatus(200);
        $response->assertSee('Modo Prueba Gratuita');
        $response->assertSessionMissing('guest_book_id');
    }

    public function test_guest_reset_query_param_clears_session_and_redirects_home(): void
    {
        $response = $this->withSession(['guest_book_id' => 1, 'guest_upload_count' => 1])->get('/?reset=1');
        $response->assertStatus(200);
        $response->assertSee('Agregar Documento o Libro');
        $response->assertSessionMissing('guest_book_id');
        $response->assertSessionMissing('guest_upload_count');
    }

    public function test_guest_can_retry_their_own_failed_book(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $book = \App\Models\Book::create([
            'user_id' => null,
            'title' => 'Libro Fallido Invitado',
            'original_filename' => 'fallido.pdf',
            'pdf_path' => 'pdfs/fallido.pdf',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'failed',
            'error_message' => 'Error previo de prueba',
        ]);

        $response = $this->withSession(['guest_book_id' => $book->id])
            ->post("/books/{$book->id}/retry");

        $response->assertRedirect(route('books.show', $book->id));
        $this->assertEquals('pending', $book->fresh()->status);
        $this->assertNull($book->fresh()->error_message);
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ProcessBookJob::class);
    }

    // ── Multi-format upload validation tests ──────────────────────────────────

    public function test_upload_validation_accepts_docx_file(): void
    {
        $user = User::factory()->create();
        $file = \Illuminate\Http\UploadedFile::fake()->create('document.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($user)->post('/books', [
            'pdf_file'   => $file,
            'voice'      => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch'      => '+0Hz',
        ]);

        // Should NOT redirect back with validation errors (accepts docx)
        $response->assertSessionHasNoErrors();
    }

    public function test_upload_validation_accepts_txt_file(): void
    {
        $user = User::factory()->create();
        $file = \Illuminate\Http\UploadedFile::fake()->create('notes.txt', 50, 'text/plain');

        $response = $this->actingAs($user)->post('/books', [
            'pdf_file'   => $file,
            'voice'      => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch'      => '+0Hz',
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_upload_validation_rejects_unsupported_formats(): void
    {
        $user = User::factory()->create();
        $exeFile = \Illuminate\Http\UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream');

        $response = $this->actingAs($user)->post('/books', [
            'pdf_file'   => $exeFile,
            'voice'      => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch'      => '+0Hz',
        ]);

        $response->assertSessionHasErrors(['pdf_file']);
    }

    public function test_upload_validation_accepts_markdown_file(): void
    {
        $user = User::factory()->create();
        $file = \Illuminate\Http\UploadedFile::fake()->create('readme.md', 30, 'text/markdown');

        $response = $this->actingAs($user)->post('/books', [
            'pdf_file'   => $file,
            'voice'      => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch'      => '+0Hz',
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

        $sampleText = "Este es un texto redactado directamente en la plataforma para su conversión rápida a voz neuronal. Contiene varias oraciones para cumplir con la longitud mínima requerida y validar el flujo integral.";

        $response = $this->actingAs($user)->post(route('books.store'), [
            'raw_text'   => $sampleText,
            'title'      => 'Artículo de Prueba Directo',
            'voice'      => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch'      => '+0Hz',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('books', [
            'title'   => 'Artículo de Prueba Directo',
            'user_id' => $user->id,
            'status'  => 'pending',
        ]);

        Queue::assertPushed(ProcessBookJob::class);
    }

    public function test_upload_validation_rejects_when_both_file_and_raw_text_are_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'voice'      => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch'      => '+0Hz',
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
                ]
            ]
        ]);
    }
}

