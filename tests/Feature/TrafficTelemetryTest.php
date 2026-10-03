<?php

namespace Tests\Feature;

use App\Models\TrafficLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrafficTelemetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_visit_records_traffic_log(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        $this->assertDatabaseHas('traffic_logs', [
            'path' => '/',
            'user_id' => null,
            'is_crawler' => false,
            'status_code' => 200,
        ]);
    }

    public function test_authenticated_user_visit_records_user_id(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/books');

        $response->assertStatus(200);

        $this->assertDatabaseHas('traffic_logs', [
            'path' => '/books',
            'user_id' => $user->id,
            'is_crawler' => false,
            'status_code' => 200,
        ]);
    }

    public function test_social_media_crawler_is_detected(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'WhatsApp/2.23.20.10 A',
        ])->get('/');

        $response->assertStatus(200);

        $this->assertDatabaseHas('traffic_logs', [
            'path' => '/',
            'is_crawler' => true,
            'device_type' => 'bot',
        ]);
    }

    public function test_static_assets_are_ignored_by_telemetry(): void
    {
        $initialCount = TrafficLog::count();

        $this->get('/images/test-icon.png');
        $this->get('/build/assets/app-1234.css');

        $this->assertEquals($initialCount, TrafficLog::count());
    }

    public function test_admin_telemetry_renders_traffic_metrics(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Seed some traffic
        TrafficLog::create([
            'ip_hash' => hash('sha256', '127.0.0.1'),
            'user_id' => null,
            'path' => '/books/24',
            'method' => 'GET',
            'status_code' => 200,
            'device_type' => 'mobile',
            'is_crawler' => false,
            'created_at' => now(),
        ]);

        TrafficLog::create([
            'ip_hash' => hash('sha256', '10.0.0.2'),
            'user_id' => null,
            'path' => '/books/24',
            'method' => 'GET',
            'status_code' => 200,
            'device_type' => 'bot',
            'is_crawler' => true,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/telemetry');

        $response->assertStatus(200);
        $response->assertSee('Conexiones, Visitas');
        $response->assertSee('Peticiones Totales');
        $response->assertSee('Rastreador Social / Bot');
    }
}
