<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\TrafficLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
    }

    public function test_guest_fingerprint_and_country_from_cloudflare_header(): void
    {
        $response = $this->withHeaders([
            'CF-IPCountry' => 'VE',
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 14; SM-S908B) AppleWebKit/537.36 Mobile Safari/537.36',
        ])->get('/');

        $response->assertStatus(200);

        $this->assertDatabaseHas('traffic_logs', [
            'path' => '/',
            'country_code' => 'VE',
            'country_name' => 'Venezuela',
            'country_flag' => '🇻🇪',
        ]);

        $log = TrafficLog::where('path', '/')->first();
        $this->assertNotNull($log->guest_fingerprint);
        $this->assertStringContainsString('VE', $log->guest_fingerprint);
        $this->assertStringContainsString('Android', $log->guest_fingerprint);
    }

    public function test_book_access_correlates_action_details_and_book_id(): void
    {
        $book = Book::create([
            'title' => 'Tratado de Medicina Integral',
            'author' => 'Héctor Mota',
            'original_filename' => 'test.pdf',
            'status' => 'ready',
            'pdf_path' => 'pdfs/test.pdf',
        ]);

        $response = $this->withHeaders([
            'CF-IPCountry' => 'ES',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ])->get("/books/{$book->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('traffic_logs', [
            'path' => "/books/{$book->id}",
            'book_id' => $book->id,
            'country_code' => 'ES',
        ]);

        $log = TrafficLog::where('path', "/books/{$book->id}")->first();
        $this->assertStringContainsString('Tratado de Medicina Integral', $log->action_details);
    }

    public function test_admin_telemetry_loads_up_to_200_connections(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Seed 35 connections (exceeding old limit of 25)
        for ($i = 1; $i <= 35; $i++) {
            TrafficLog::create([
                'ip_hash' => hash('sha256', "10.0.0.{$i}"),
                'user_id' => null,
                'path' => "/test-route-{$i}",
                'method' => 'GET',
                'status_code' => 200,
                'country_code' => $i % 2 === 0 ? 'VE' : 'ES',
                'country_name' => $i % 2 === 0 ? 'Venezuela' : 'España',
                'country_flag' => $i % 2 === 0 ? '🇻🇪' : '🇪🇸',
                'guest_fingerprint' => "Invitado-Test #{$i}",
                'action_details' => "Ruta de prueba #{$i}",
                'device_type' => 'mobile',
                'is_crawler' => false,
                'created_at' => now(),
            ]);
        }

        $response = $this->actingAs($admin)->get('/admin/telemetry');

        $response->assertStatus(200);
        $response->assertSee('Últimas 200 Conexiones al Servidor');
        $response->assertSee('/test-route-1');
        $response->assertSee('/test-route-35');
        $response->assertSee('Países de Origen (GeoIP)');
        $response->assertSee('Venezuela');
        $response->assertSee('España');
    }

    public function test_guest_uploading_book_persists_fingerprint_and_country(): void
    {
        $file = UploadedFile::fake()->create('guia.pdf', 10, 'application/pdf');

        $response = $this->withHeaders([
            'CF-IPCountry' => 'CL',
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
        ])->post('/books', [
            'pdf_file' => $file,
            'voice' => 'es_ES-davefx-medium',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response->assertStatus(302);

        $book = Book::latest('id')->first();
        $this->assertNotNull($book);
        $this->assertEquals('CL', $book->country_code);
        $this->assertNotNull($book->guest_fingerprint);
        $this->assertStringContainsString('CL', $book->guest_fingerprint);
        $this->assertStringContainsString('iOS', $book->guest_fingerprint);
    }
}
