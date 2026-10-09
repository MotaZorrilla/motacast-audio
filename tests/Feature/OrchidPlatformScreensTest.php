<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\SupportTicket;
use App\Models\TrafficLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrchidPlatformScreensTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@motacast.test',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
            'permissions' => ['platform.index' => true, 'platform.systems.users' => true],
        ]);

        $this->regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@motacast.test',
            'password' => bcrypt('secret123'),
            'role' => 'user',
            'permissions' => [],
        ]);
    }

    public function test_guest_is_redirected_from_orchid_dashboard(): void
    {
        $response = $this->get('/orchid');
        $response->assertStatus(302);
    }

    public function test_admin_can_access_orchid_main_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('platform.main'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_orchid_books_screen(): void
    {
        Book::create([
            'user_id' => $this->admin->id,
            'title' => 'Libro Orchid Test',
            'original_filename' => 'test.pdf',
            'pdf_path' => 'pdfs/test.pdf',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
            'status' => 'ready',
            'total_chapters' => 3,
            'processed_chapters' => 3,
            'total_duration' => 180,
        ]);

        $response = $this->actingAs($this->admin)->get(route('platform.books'));
        $response->assertStatus(200);
        $response->assertSee('Gestión de Audiolibros');
        $response->assertSee('Libro Orchid Test');
        $response->assertSee('Total Documentos');
    }

    public function test_admin_can_access_orchid_telemetry_screen(): void
    {
        TrafficLog::create([
            'ip_hash' => hash('sha256', '127.0.0.1'),
            'country_code' => 'VE',
            'country_name' => 'Venezuela',
            'country_flag' => '🇻🇪',
            'path' => '/books',
            'device_type' => 'mobile',
            'status_code' => 200,
        ]);

        $response = $this->actingAs($this->admin)->get(route('platform.telemetry'));
        $response->assertStatus(200);
        $response->assertSee('Telemetría de Tráfico Global');
        $response->assertSee('/books');
        $response->assertSee('VE');
    }

    public function test_admin_can_access_orchid_tickets_screen(): void
    {
        SupportTicket::create([
            'user_id' => $this->regularUser->id,
            'type' => 'extension_limite',
            'subject' => 'Necesito 5 libros más',
            'message' => 'Por favor habiliten 5 libros adicionales para mi tesis.',
            'status' => 'pendiente',
            'requested_books' => 5,
        ]);

        $response = $this->actingAs($this->admin)->get(route('platform.tickets'));
        $response->assertStatus(200);
        $response->assertSee('Mesa de Ayuda & Cuotas');
        $response->assertSee('Necesito 5 libros más');
        $response->assertSee('Solicitud de Cuota');
    }
}
