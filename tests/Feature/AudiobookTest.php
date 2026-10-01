<?php

namespace Tests\Feature;

use App\Jobs\ProcessBookJob;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;
use App\Services\AudioSynthesisService;
use App\Services\PdfExtractorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class AudiobookTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Usuario Lector',
            'email' => 'lector@motazorrilla.com',
            'role' => 'user',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Héctor Mota Admin',
            'email' => 'admin@motazorrilla.com',
            'role' => 'admin',
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_books(): void
    {
        $response = $this->get('/books');
        $response->assertRedirect('/login');
    }

    public function test_user_can_register_and_login(): void
    {
        $response = $this->post('/register', [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@motazorrilla.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@motazorrilla.com',
            'role' => 'user',
        ]);
    }

    public function test_books_index_page_is_accessible_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get('/books');

        $response->assertStatus(200);
        $response->assertSee('MotaCastAudio');
        $response->assertSee('Mis Documentos');
    }

    public function test_create_form_displays_neural_voices_and_friendly_copy(): void
    {
        $response = $this->actingAs($this->user)->get('/books/create');

        $response->assertStatus(200);
        $response->assertSee('Agregar Documento o Libro');
        $response->assertSee('es-VE-SebastianNeural');
        $response->assertSee('Sebastián');
        // Verify technical jargon was removed from the form as requested
        $response->assertDontSee('PyMuPDF');
        $response->assertDontSee('edge-tts');
    }

    public function test_unhappy_path_upload_rejects_missing_file(): void
    {
        $response = $this->actingAs($this->user)->post('/books', []);

        $response->assertSessionHasErrors(['pdf_file', 'voice', 'speed_rate', 'pitch']);
    }

    public function test_unhappy_path_upload_rejects_non_pdf_file(): void
    {
        Storage::fake('public');

        $fakeZip = UploadedFile::fake()->create('malware.zip', 50, 'application/zip');

        $response = $this->actingAs($this->user)->post('/books', [
            'pdf_file' => $fakeZip,
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response->assertSessionHasErrors(['pdf_file']);
    }

    public function test_successful_pdf_upload_creates_book_with_user_and_dispatches_job(): void
    {
        Storage::fake('public');
        Queue::fake();

        $pdf = UploadedFile::fake()->create('guia_homelab.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->user)->post('/books', [
            'pdf_file' => $pdf,
            'title' => 'Guía Homelab Mota Zorrilla',
            'author' => 'Héctor Mota',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $this->assertDatabaseHas('books', [
            'title' => 'Guía Homelab Mota Zorrilla',
            'author' => 'Héctor Mota',
            'user_id' => $this->user->id,
            'voice' => 'es-VE-SebastianNeural',
            'status' => 'pending',
        ]);

        $book = Book::first();

        Queue::assertPushed(ProcessBookJob::class, function ($job) use ($book) {
            return $job->bookId === $book->id;
        });

        $response->assertRedirect(route('books.show', $book->id));
        $response->assertSessionHas('success');
    }

    public function test_book_show_page_renders_player_and_chapter_data(): void
    {
        $book = Book::create([
            'user_id' => $this->user->id,
            'title' => 'Manual de Arquitectura Cloudflare',
            'author' => 'Héctor Mota Zorrilla',
            'original_filename' => 'manual.pdf',
            'pdf_path' => 'pdfs/fake.pdf',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'ready',
            'total_chapters' => 2,
            'processed_chapters' => 2,
            'total_duration' => 360,
            'total_words' => 1200,
        ]);

        Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Capítulo 1: Configuración de Túneles',
            'content_text' => 'Texto del primer capítulo...',
            'audio_path' => 'audiobooks/'.$book->id.'/chapter_1.mp3',
            'duration_seconds' => 180,
            'word_count' => 600,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($this->user)->get(route('books.show', $book->id));

        $response->assertStatus(200);
        $response->assertSee('Manual de Arquitectura Cloudflare');
        $response->assertSee('Capítulo 1: Configuración de Túneles');
        $response->assertSee('06:00'); // formatted total duration
    }

    public function test_user_isolation_user_b_cannot_view_user_a_book(): void
    {
        $otherUser = User::factory()->create(['role' => 'user']);

        $book = Book::create([
            'user_id' => $this->user->id,
            'title' => 'Documento Privado de Usuario A',
            'original_filename' => 'privado.pdf',
            'pdf_path' => 'pdfs/privado.pdf',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($otherUser)->get(route('books.show', $book->id));
        $response->assertStatus(403);
    }

    public function test_user_isolation_user_b_cannot_stream_user_a_chapter(): void
    {
        $otherUser = User::factory()->create(['role' => 'user']);

        $book = Book::create([
            'user_id' => $this->user->id,
            'title' => 'Documento Privado de Usuario A',
            'original_filename' => 'privado.pdf',
            'pdf_path' => 'pdfs/privado.pdf',
            'status' => 'ready',
        ]);

        $chapter = Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Capítulo Privado',
            'content_text' => 'Texto...',
            'audio_path' => 'audiobooks/test.mp3',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($otherUser)->get(route('chapters.stream', $chapter->id));
        $response->assertStatus(403);
    }

    public function test_user_isolation_user_b_cannot_delete_user_a_book(): void
    {
        $otherUser = User::factory()->create(['role' => 'user']);

        $book = Book::create([
            'user_id' => $this->user->id,
            'title' => 'Documento No Borrable',
            'original_filename' => 'noborrar.pdf',
            'pdf_path' => 'pdfs/noborrar.pdf',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($otherUser)->delete(route('books.destroy', $book->id));
        $response->assertStatus(403);

        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_admin_can_view_and_delete_any_users_book(): void
    {
        Storage::fake('public');

        $pdfPath = 'pdfs/book_admin_test.pdf';
        Storage::disk('public')->put($pdfPath, '%PDF-1.4 Fake PDF');

        $book = Book::create([
            'user_id' => $this->user->id,
            'title' => 'Documento de Usuario Estándar',
            'original_filename' => 'usuario.pdf',
            'pdf_path' => $pdfPath,
            'status' => 'ready',
        ]);

        // Admin can view
        $viewResponse = $this->actingAs($this->admin)->get(route('books.show', $book->id));
        $viewResponse->assertStatus(200);

        // Admin can delete
        $delResponse = $this->actingAs($this->admin)->delete(route('books.destroy', $book->id));
        $delResponse->assertRedirect(route('books.index'));

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_status_api_endpoint_returns_json_progress(): void
    {
        $book = Book::create([
            'user_id' => $this->user->id,
            'title' => 'Libro de Pruebas API',
            'original_filename' => 'test.pdf',
            'pdf_path' => 'pdfs/test.pdf',
            'status' => 'synthesizing',
            'total_chapters' => 4,
            'processed_chapters' => 2,
            'total_duration' => 240,
        ]);

        Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Capítulo 1',
            'content_text' => 'Contenido...',
            'audio_path' => 'audiobooks/'.$book->id.'/chapter_1.mp3',
            'duration_seconds' => 120,
            'word_count' => 300,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($this->user)->get(route('books.status', $book->id));

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $book->id,
            'status' => 'synthesizing',
            'progress' => 50,
            'total_chapters' => 4,
            'processed_chapters' => 2,
        ]);

        $response->assertJsonStructure([
            'id', 'status', 'progress', 'total_chapters', 'processed_chapters', 'total_duration', 'chapters' => [
                '*' => ['id', 'chapter_number', 'title', 'status', 'duration', 'audio_url', 'download_url'],
            ],
        ]);
    }

    public function test_chapter_audio_streaming_returns_partial_content_with_byte_range(): void
    {
        Storage::fake('public');

        $book = Book::create([
            'user_id' => $this->user->id,
            'title' => 'Stream Test Book',
            'original_filename' => 'test.pdf',
            'pdf_path' => 'pdfs/test.pdf',
            'status' => 'ready',
        ]);

        $audioContent = str_repeat('MP3_DUMMY_BYTE_DATA_STREAM_', 100);
        $audioPath = "audiobooks/{$book->id}/chapter_1.mp3";
        Storage::disk('public')->put($audioPath, $audioContent);

        $chapter = Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Capítulo Stream',
            'content_text' => 'Texto...',
            'audio_path' => $audioPath,
            'duration_seconds' => 60,
            'status' => 'ready',
        ]);

        // Request with Range header
        $response = $this->actingAs($this->user)->withHeaders([
            'Range' => 'bytes=0-49',
        ])->get(route('chapters.stream', $chapter->id));

        $response->assertStatus(206);
        $response->assertHeader('Content-Range');
        $response->assertHeader('Accept-Ranges', 'bytes');
        $response->assertHeader('Content-Type', 'audio/mpeg');
    }

    public function test_book_deletion_removes_database_records_and_files(): void
    {
        Storage::fake('public');

        $pdfPath = 'pdfs/to_delete.pdf';
        Storage::disk('public')->put($pdfPath, '%PDF-1.4 Fake PDF');

        $book = Book::create([
            'user_id' => $this->user->id,
            'title' => 'Libro a Eliminar',
            'original_filename' => 'to_delete.pdf',
            'pdf_path' => $pdfPath,
            'status' => 'ready',
        ]);

        $audioPath = "audiobooks/{$book->id}/chapter_1.mp3";
        Storage::disk('public')->put($audioPath, 'AUDIO_DATA');

        $chapter = Chapter::create([
            'book_id' => $book->id,
            'chapter_number' => 1,
            'title' => 'Capítulo 1',
            'content_text' => 'Texto',
            'audio_path' => $audioPath,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($this->user)->delete(route('books.destroy', $book->id));

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $this->assertDatabaseMissing('chapters', ['id' => $chapter->id]);

        Storage::disk('public')->assertMissing($pdfPath);
    }

    public function test_pdf_extractor_service_segments_multichapter_pdf_correctly(): void
    {
        $extractor = new PdfExtractorService;
        $samplePdf = storage_path('app/test_sample.pdf');

        $script = "import pymupdf as fitz
doc = fitz.open()
p1 = doc.new_page()
p1.insert_text((50, 72), 'Capitulo 1: Introduccion a MotaZorrilla\\nEste es el texto del capitulo uno sobre arquitectura web.', fontsize=12)
p2 = doc.new_page()
p2.insert_text((50, 72), 'Capitulo 2: Despliegue en Homelab\\nEste es el segundo capitulo explicando servidores Linux.', fontsize=12)
doc.save(r'{$samplePdf}')
doc.close()
";
        $process = new Process(['python', '-c', $script]);
        $process->run();

        $this->assertFileExists($samplePdf);

        try {
            $result = $extractor->extract($samplePdf);
            $this->assertTrue($result['success']);
            $this->assertCount(2, $result['chapters']);
            $this->assertEquals(1, $result['chapters'][0]['chapter_number']);
            $this->assertEquals(2, $result['chapters'][1]['chapter_number']);
        } finally {
            if (file_exists($samplePdf)) {
                @unlink($samplePdf);
            }
        }
    }

    public function test_unhappy_path_scanned_pdf_without_ocr_triggers_clear_error(): void
    {
        $extractor = new PdfExtractorService;
        $blankPdf = storage_path('app/blank_test.pdf');

        $script = "import pymupdf as fitz
doc = fitz.open()
doc.new_page() # blank page
doc.save(r'{$blankPdf}')
doc.close()
";
        $process = new Process(['python', '-c', $script]);
        $process->run();

        $this->assertTrue(file_exists($blankPdf), 'Blank PDF could not be created for the test.');

        try {
            $extractor->extract($blankPdf);
            // If OCR is installed and processes an empty page with 0 words, it should still throw
            $this->fail('Expected an Exception to be thrown for a blank/scanned PDF.');
        } catch (\Exception $e) {
            // Accept any of the valid error messages: no text, OCR not installed, or OCR ran but still no words
            $msg = $e->getMessage();
            $validMessages = [
                'no contiene texto legible',
                'tesseract',
                'pytesseract',
                'ocr',
                'extractor de pdf',
            ];
            $matched = false;
            foreach ($validMessages as $phrase) {
                if (stripos($msg, $phrase) !== false) {
                    $matched = true;
                    break;
                }
            }
            $this->assertTrue($matched, "Unexpected error message: {$msg}");
        } finally {
            if (file_exists($blankPdf)) {
                @unlink($blankPdf);
            }
        }
    }

    public function test_audio_synthesis_service_generates_valid_mp3_audio(): void
    {
        $synthesizer = new AudioSynthesisService;
        $tempMp3 = storage_path('app/temp_test_synthesis.mp3');

        if (file_exists($tempMp3)) {
            @unlink($tempMp3);
        }

        try {
            $result = $synthesizer->synthesize(
                text: 'Prueba de síntesis de voz neuronal en el ecosistema Mota Zorrilla.',
                outputAbsolutePath: $tempMp3,
                voice: 'es-VE-SebastianNeural'
            );

            $this->assertTrue($result['success']);
            $this->assertFileExists($tempMp3);
            $this->assertGreaterThan(0, $result['duration_seconds']);
            $this->assertEquals('es-VE-SebastianNeural', $result['voice']);
        } finally {
            if (file_exists($tempMp3)) {
                @unlink($tempMp3);
            }
        }
    }

    public function test_admin_can_access_users_management_and_see_stats(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee('Gestión de Usuarios y Límites');
        $response->assertSee('Total Usuarios');
        $response->assertSee($this->user->name);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.users.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_create_user_with_custom_quota(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Carlos Estudiante',
            'email' => 'carlos@motazorrilla.com',
            'password' => 'carlos123',
            'role' => 'user',
            'status' => 'active',
            'book_limit' => 5,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'carlos@motazorrilla.com',
            'book_limit' => 5,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_adjust_user_quota_with_quick_actions(): void
    {
        $targetUser = User::factory()->create(['book_limit' => 3]);

        // Test +1
        $this->actingAs($this->admin)->post(route('admin.users.adjust-limit', $targetUser->id), [
            'action' => 'plus1',
        ]);
        $this->assertEquals(4, $targetUser->fresh()->book_limit);

        // Test +5
        $this->actingAs($this->admin)->post(route('admin.users.adjust-limit', $targetUser->id), [
            'action' => 'plus5',
        ]);
        $this->assertEquals(9, $targetUser->fresh()->book_limit);

        // Test Unlimited
        $this->actingAs($this->admin)->post(route('admin.users.adjust-limit', $targetUser->id), [
            'action' => 'unlimited',
        ]);
        $this->assertEquals(-1, $targetUser->fresh()->book_limit);

        // Test Reset
        $this->actingAs($this->admin)->post(route('admin.users.adjust-limit', $targetUser->id), [
            'action' => 'reset',
        ]);
        $this->assertEquals(3, $targetUser->fresh()->book_limit);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $targetUser = User::factory()->create(['status' => 'active']);

        $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $targetUser->id));
        $this->assertEquals('suspended', $targetUser->fresh()->status);

        $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $targetUser->id));
        $this->assertEquals('active', $targetUser->fresh()->status);
    }

    public function test_admin_cannot_suspend_or_demote_himself(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $this->admin->id));
        $response->assertSessionHas('error');
        $this->assertEquals('active', $this->admin->fresh()->status);

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin->id), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => 'user', // Trying to demote
            'status' => 'suspended',
            'book_limit' => 1,
        ]);
        $updateResponse->assertSessionHas('error');
        $this->assertEquals('admin', $this->admin->fresh()->role);
    }

    public function test_user_quota_blocks_upload_when_exceeded(): void
    {
        Storage::fake('public');

        $limitedUser = User::factory()->create(['book_limit' => 1]);

        // First upload succeeds
        Book::create([
            'user_id' => $limitedUser->id,
            'title' => 'Libro 1',
            'original_filename' => 'l1.pdf',
            'pdf_path' => 'pdfs/l1.pdf',
            'status' => 'ready',
        ]);

        $pdf = UploadedFile::fake()->create('libro2.pdf', 100, 'application/pdf');

        $response = $this->actingAs($limitedUser)->post('/books', [
            'pdf_file' => $pdf,
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('error');
        $this->assertEquals(1, $limitedUser->books()->count());
    }

    public function test_guest_can_upload_one_trial_book_then_must_register(): void
    {
        Storage::fake('public');
        Queue::fake();

        $pdf = UploadedFile::fake()->create('trial.pdf', 100, 'application/pdf');

        // First trial upload as guest
        $response = $this->post('/books', [
            'pdf_file' => $pdf,
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(1, session('guest_upload_count'));

        // Second attempt as guest should redirect to register
        $response2 = $this->get('/books/create');
        $response2->assertRedirect(route('register'));
        $response2->assertSessionHas('info');
    }

    public function test_pdf_stream_inline_response_is_accessible(): void
    {
        Storage::fake('public');

        $pdfPath = 'pdfs/stream_test.pdf';
        Storage::disk('public')->put($pdfPath, '%PDF-1.4 Stream Test Content');

        $book = Book::create([
            'user_id' => $this->user->id,
            'title' => 'PDF Stream Book',
            'original_filename' => 'stream_test.pdf',
            'pdf_path' => $pdfPath,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($this->user)->get(route('books.pdf', $book->id));
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'inline; filename="stream_test.pdf"');
    }

    public function test_nlp_sentence_continuity_normalizer_removes_unnatural_line_breaks(): void
    {
        $input = "Esta es la primera línea\nde un texto técnico que no debe\ntener pausas innecesarias.\n\nEste es un nuevo párrafo.\nTiene una segunda oración.";
        $normalized = AudioSynthesisService::normalizeText($input);

        $expected = "Esta es la primera línea de un texto técnico que no debe tener pausas innecesarias.\n\nEste es un nuevo párrafo. Tiene una segunda oración.";
        $this->assertEquals($expected, $normalized);
    }
}
