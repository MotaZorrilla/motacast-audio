<?php

namespace Tests\Feature;

use App\Jobs\ProcessBookJob;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GuestTrialWorkflowTest extends TestCase
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
        $book = Book::create([
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
        $book = Book::create([
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
        $admin = User::factory()->create(['role' => 'admin']);
        $book = Book::create([
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
        Queue::fake();

        $book = Book::create([
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
        Queue::assertPushed(ProcessBookJob::class);
    }

    public function test_register_claims_guest_trial_book_and_redirects_to_it(): void
    {
        $book = Book::create([
            'user_id' => null,
            'title' => 'Libro Creado en Modo Invitado',
            'original_filename' => 'invitado.pdf',
            'pdf_path' => 'pdfs/invitado.pdf',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'ready',
        ]);

        $response = $this->withSession([
            'guest_book_id' => $book->id,
            'guest_upload_count' => 1,
        ])->post('/register', [
            'name' => 'Usuario Recién Registrado',
            'email' => 'recien_registrado@motazorrilla.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('books.show', $book->id));

        $user = User::where('email', 'recien_registrado@motazorrilla.com')->first();
        $this->assertNotNull($user);

        // Verify the book was claimed and transferred
        $this->assertEquals($user->id, $book->fresh()->user_id);

        // Verify visiting /books displays the claimed book in the user library
        $libraryResponse = $this->actingAs($user)->get('/books');
        $libraryResponse->assertStatus(200);
        $libraryResponse->assertSee('Libro Creado en Modo Invitado');
    }

    public function test_admin_can_filter_guest_books_in_catalog(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $regularUser = User::factory()->create(['role' => 'user']);

        $userBook = Book::create([
            'user_id' => $regularUser->id,
            'title' => 'Libro Exclusivo de Usuario Registrado',
            'original_filename' => 'user.pdf',
            'pdf_path' => 'pdfs/user.pdf',
            'status' => 'ready',
        ]);

        $guestBook = Book::create([
            'user_id' => null,
            'title' => 'Libro Huérfano de Invitado Anónimo',
            'original_filename' => 'guest.pdf',
            'pdf_path' => 'pdfs/guest.pdf',
            'guest_fingerprint' => '🇻🇪 Invitado-VE · Android #999',
            'country_code' => 'VE',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($admin)->get('/books?user_id=guests');
        $response->assertStatus(200);
        $response->assertSee('Libro Huérfano de Invitado Anónimo');
        $response->assertSee('Invitado-VE · Android #999');
        $response->assertSee('Mostrando libros de Invitados Anónimos');
        $response->assertDontSee('Libro Exclusivo de Usuario Registrado');
    }
}
