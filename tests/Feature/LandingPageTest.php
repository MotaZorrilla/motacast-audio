<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_visits_landing_page_at_root(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Transforma tus Libros y Documentos en');
        $response->assertSee('Podcasts Personales');
        $response->assertSee('Agregar Documento o Libro');
        $response->assertSee('Álvaro (España)');
        $response->assertSee('Elvira (España)');
        $response->assertSee('Jorge (México)');
        $response->assertSee('Sebastián (Venezuela)');
        $response->assertSee('Early Access');
    }

    public function test_authenticated_user_accessing_root_sees_library(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('Mis Documentos');
    }

    public function test_voice_sample_preview_endpoint_returns_audio(): void
    {
        $response = $this->get(route('voices.preview', [
            'voice' => 'es-ES-AlvaroNeural',
            'speed' => '+0%',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('audio/', $response->headers->get('Content-Type'));
    }
}
