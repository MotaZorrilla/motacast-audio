<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelemetryAndTicketsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_telemetry_and_see_metrics(): void
    {
        $admin = User::create([
            'name' => 'Admin Telemetría',
            'email' => 'admin.telemetry@motazorrilla.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.telemetry.index'));
        $response->assertStatus(200);
        $response->assertSee('Telemetría & Métricas');
        $response->assertSee('Observabilidad en Tiempo Real');
        $response->assertSee('Espacio en Audios');
    }

    public function test_non_admin_cannot_access_telemetry(): void
    {
        $user = User::create([
            'name' => 'Usuario Normal',
            'email' => 'user.normal@motazorrilla.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('admin.telemetry.index'));
        $response->assertStatus(403);
    }

    public function test_user_can_submit_support_ticket(): void
    {
        $user = User::create([
            'name' => 'Usuario Reporte',
            'email' => 'reporte@motazorrilla.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->post(route('tickets.store'), [
            'type' => 'bug',
            'subject' => 'Audio se cortó en capítulo 2',
            'message' => 'Al reproducir el minuto 3 hubo un silencio largo.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $user->id,
            'type' => 'bug',
            'subject' => 'Audio se cortó en capítulo 2',
            'status' => 'pendiente',
        ]);
    }

    public function test_user_can_request_auto_courtesy_extension_once(): void
    {
        $user = User::create([
            'name' => 'Usuario Beta',
            'email' => 'beta@motazorrilla.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'book_limit' => 3,
            'auto_extension_used' => false,
        ]);

        $response = $this->actingAs($user)->post(route('tickets.request-extension'));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals(4, $user->book_limit);
        $this->assertTrue($user->auto_extension_used);

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $user->id,
            'type' => 'extension_limite',
            'status' => 'resuelto',
        ]);
    }

    public function test_user_second_extension_request_creates_pending_admin_ticket(): void
    {
        $user = User::create([
            'name' => 'Usuario Beta Frecuente',
            'email' => 'frecuente@motazorrilla.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'book_limit' => 4,
            'auto_extension_used' => true,
        ]);

        $response = $this->actingAs($user)->post(route('tickets.request-extension'), [
            'reason' => 'Requiero convertir 2 libros más para mi proyecto final.',
            'requested_books' => 2,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        // Quota should not change automatically on second request
        $this->assertEquals(4, $user->book_limit);

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $user->id,
            'type' => 'extension_limite',
            'status' => 'pendiente',
            'requested_books' => 2,
        ]);
    }

    public function test_admin_can_approve_extension_ticket_with_one_click(): void
    {
        $admin = User::create([
            'name' => 'Admin Boss',
            'email' => 'boss@motazorrilla.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'Estudiante Necesitado',
            'email' => 'estudiante@motazorrilla.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
            'book_limit' => 3,
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'guest_email' => $user->email,
            'type' => 'extension_limite',
            'subject' => 'Solicitud de libros extra',
            'message' => 'Necesito 3 libros más',
            'status' => 'pendiente',
            'requested_books' => 3,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.tickets.approve-extension', $ticket->id), [
            'books' => 3,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $ticket->refresh();

        $this->assertEquals(6, $user->book_limit);
        $this->assertEquals('resuelto', $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
    }

    public function test_admin_can_reply_to_ticket(): void
    {
        $admin = User::create([
            'name' => 'Admin Soporte',
            'email' => 'soporte@motazorrilla.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => null,
            'guest_email' => 'invitado@correo.com',
            'type' => 'consulta',
            'subject' => '¿Qué voces están disponibles?',
            'message' => 'Quiero saber si hay acento argentino.',
            'status' => 'pendiente',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.tickets.reply', $ticket->id), [
            'status' => 'resuelto',
            'admin_reply' => 'Actualmente contamos con voces neuronales en español latinoamericano y castellano.',
        ]);

        $response->assertRedirect();
        $ticket->refresh();

        $this->assertEquals('resuelto', $ticket->status);
        $this->assertStringContainsString('latinoamericano', $ticket->admin_reply);
    }

    public function test_registration_with_beta_disclaimer_sets_timestamp(): void
    {
        $response = $this->post('/register', [
            'name' => 'Beta Tester',
            'email' => 'tester@motazorrilla.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'beta_disclaimer' => '1',
        ]);

        $response->assertRedirect(route('books.index'));

        $user = User::where('email', 'tester@motazorrilla.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->beta_disclaimer_accepted_at);
    }
}
