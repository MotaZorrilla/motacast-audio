<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageAndOrchidUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_visits_root_and_sees_kairos_landing_sections(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Hero & CTA
        $response->assertSee('Convierte Documentos en', false);
        $response->assertSee('Audiolibros Neuronales', false);
        $response->assertSee('Probar Conversión Gratis', false);
        $response->assertSee('Escuchar Muestra de Voz', false);

        // Required Sections (Kairos App Landing Architecture)
        $response->assertSee('id="inicio"', false);
        $response->assertSee('id="probar"', false);
        $response->assertSee('id="como-funciona"', false);
        $response->assertSee('id="caracteristicas"', false);
        $response->assertSee('id="demo"', false);
        $response->assertSee('id="precios"', false);

        // Trial Engine Form Compatibility
        $response->assertSee('Agregar Documento o Libro', false);
        $response->assertSee('Modo Prueba Gratuita', false);
    }

    public function test_guest_visits_landing_route_explicitly(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        $response->assertSee('MotaCastAudio v2.7 · IA & Síntesis Neuronal', false);
        $response->assertSee('¿Cómo usar MotaCastAudio? — Fácil en 3 Pasos', false);
        $response->assertSee('Sube tu archivo o pega tu texto', false);
    }

    public function test_authenticated_user_accesses_library_with_orchid_sidebar_and_kpis(): void
    {
        $user = User::factory()->create([
            'name' => 'Héctor Mota',
            'email' => 'hector@motazorrilla.com',
            'book_limit' => 10,
        ]);

        Book::create([
            'user_id' => $user->id,
            'title' => 'Manual de Inteligencia Artificial',
            'original_filename' => 'ia_manual.pdf',
            'pdf_path' => 'pdfs/ia_manual.pdf',
            'status' => 'ready',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response = $this->actingAs($user)->get(route('books.index'));

        $response->assertStatus(200);

        // Orchid Desktop Sidebar
        $response->assertSee('id="orchidSidebar"', false);
        $response->assertSee('Biblioteca', false);
        $response->assertSee('Mis Documentos', false);
        $response->assertSee('Subir Documento', false);
        $response->assertSee('Cuota de Libros', false);

        // Orchid KPI Metrics Cards
        $response->assertSee('Documentos', false);
        $response->assertSee('Listos', false);
        $response->assertSee('En Síntesis', false);
        $response->assertSee('Horas de Audio', false);
        $response->assertSee('Manual de Inteligencia Artificial', false);
    }

    public function test_authenticated_user_sees_mobile_orchid_drawer_and_dock(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('books.index'));

        $response->assertStatus(200);

        // Mobile Slide-Over Drawer
        $response->assertSee('id="orchidMobileDrawer"', false);
        $response->assertSee('id="orchidMobileMenuBtn"', false);
        $response->assertSee('id="orchidMobileDrawerCloseBtn"', false);

        // Mobile Bottom Thumb Dock
        $response->assertSee('dockSoundwave', false);
        $response->assertSee('Menú', false);
        $response->assertSee('Libros', false);
        $response->assertSee('Soporte', false);
    }

    public function test_admin_sees_orchid_administration_links(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertSee('Administración', false);
        $response->assertSee(route('admin.users.index'), false);
        $response->assertSee(route('admin.telemetry.index'), false);
        $response->assertSee(route('admin.tickets.index'), false);
    }

    public function test_craft_floor_zero_native_alerts_and_custom_delete_modal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('books.index'));

        $response->assertStatus(200);

        // Non-blocking delete confirmation modal
        $response->assertSee('id="deleteDocModal"', false);
        $response->assertSee('confirmDeleteDocument', false);

        // No native confirm() calls in delete action
        $response->assertDontSee('onsubmit="return confirm(', false);

        // Non-blocking Toast engine present
        $response->assertSee('window.showAppToast', false);
    }
}
