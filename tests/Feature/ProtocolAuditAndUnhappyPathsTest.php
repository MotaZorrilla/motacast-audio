<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProtocolAuditAndUnhappyPathsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Protocolo 4 Craft Floor: Verify static elimination of native browser dialogs in all Blade views.
     */
    public function test_zero_native_confirm_or_alert_in_all_blade_views(): void
    {
        $viewsPath = resource_path('views');
        $files = File::allFiles($viewsPath);

        $violatingFiles = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'php' && str_ends_with($file->getFilename(), '.blade.php')) {
                $content = File::get($file->getRealPath());

                // Strip HTML and JS comments before scanning for executable calls
                $stripped = preg_replace('/<!--.*?-->/s', '', $content);
                $stripped = preg_replace('/\/\/.*?$/m', '', $stripped);
                $stripped = preg_replace('/\/\*.*?\*\//s', '', $stripped);

                // Detect native confirm(...) or alert(...) calls
                // Exclude definitions/calls like showAppConfirm or confirmFormSubmit or showAppToast
                if (preg_match('/(?<!showApp|confirmFormSubmit)\bconfirm\s*\(/i', $stripped) ||
                    preg_match('/(?<!showAppToast)\balert\s*\(/i', $stripped)) {
                    $violatingFiles[] = $file->getRelativePathname();
                }
            }
        }

        $this->assertEmpty(
            $violatingFiles,
            'Found native confirm() or alert() calls in the following Blade views: '.implode(', ', $violatingFiles)
        );
    }

    /**
     * Protocolo 2 & 4: Global confirm modal and non-blocking helpers exist in layout.
     */
    public function test_global_confirm_modal_rendered_in_authenticated_views(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertSee('id="appGlobalConfirmModal"', false);
        $response->assertSee('window.showAppConfirm', false);
        $response->assertSee('window.confirmFormSubmit', false);
        $response->assertSee('id="appGlobalConfirmActionBtn"', false);
    }

    /**
     * Protocolo 3 Unhappy Path: Fuzzing and invalid parameters in voice preview endpoint fallback safely.
     */
    public function test_unhappy_path_voice_preview_fuzzing_falls_back_to_defaults(): void
    {
        // Invalid voice, invalid speed, invalid pitch
        $response = $this->get('/voices/preview?voice=hack_neural_injection&speed_rate=+9999%&pitch=-5000Hz');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'audio/mpeg');
    }

    /**
     * Protocolo 3 Unhappy Path: Multi-tenant RBAC prevents unauthorized access to private books.
     */
    public function test_unhappy_path_user_cannot_access_another_users_book(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();

        $book = Book::create([
            'user_id' => $owner->id,
            'title' => 'Documento Privado de Finanzas',
            'original_filename' => 'finanzas.pdf',
            'pdf_path' => 'pdfs/finanzas.pdf',
            'status' => 'ready',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        // Attacker attempts to view book
        $response = $this->actingAs($attacker)->get(route('books.show', $book->id));
        $response->assertStatus(403);

        // Attacker attempts to delete book
        $deleteResponse = $this->actingAs($attacker)->delete(route('books.destroy', $book->id));
        $deleteResponse->assertStatus(403);

        // Book remains untouched in DB
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    /**
     * Protocolo 3 Unhappy Path: Standard user cannot access admin control panels.
     */
    public function test_unhappy_path_standard_user_cannot_access_admin_panels(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        // Users panel
        $response1 = $this->actingAs($user)->get(route('admin.users.index'));
        $this->assertTrue(in_array($response1->status(), [403, 302]));

        // Telemetry panel
        $response2 = $this->actingAs($user)->get(route('admin.telemetry.index'));
        $this->assertTrue(in_array($response2->status(), [403, 302]));

        // Tickets panel
        $response3 = $this->actingAs($user)->get(route('admin.tickets.index'));
        $this->assertTrue(in_array($response3->status(), [403, 302]));
    }

    /**
     * Protocolo 3 Unhappy Path: Book upload validation rejects invalid or malicious files.
     */
    public function test_unhappy_path_book_upload_rejects_unsupported_file_extensions(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['book_limit' => 5]);

        $invalidFile = UploadedFile::fake()->create('malicious_script.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($user)->post(route('books.store'), [
            'pdf_file' => $invalidFile,
            'input_mode' => 'file',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response->assertSessionHasErrors(['pdf_file']);
        $this->assertDatabaseCount('books', 0);
    }

    /**
     * Protocolo 3 Unhappy Path: Text input validation rejects empty or too short text.
     */
    public function test_unhappy_path_text_input_requires_minimum_length(): void
    {
        $user = User::factory()->create(['book_limit' => 5]);

        $response = $this->actingAs($user)->post(route('books.store'), [
            'raw_text' => 'Hola', // Less than 10 characters
            'input_mode' => 'text',
            'voice' => 'es-VE-SebastianNeural',
        ]);

        $response->assertSessionHasErrors(['raw_text']);
        $this->assertDatabaseCount('books', 0);
    }

    /**
     * Protocolo 4 Craft Floor: Player view show.blade.php uses non-blocking confirm handlers.
     */
    public function test_craft_floor_player_view_uses_non_blocking_confirm_handlers(): void
    {
        $user = User::factory()->create();
        $book = Book::create([
            'user_id' => $user->id,
            'title' => 'Guía de Kubernetes',
            'original_filename' => 'k8s.pdf',
            'pdf_path' => 'pdfs/k8s.pdf',
            'status' => 'ready',
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $response = $this->actingAs($user)->get(route('books.show', $book->id));

        $response->assertStatus(200);
        $response->assertSee('confirmFormSubmit', false);
        $response->assertDontSee('onsubmit="return confirm(', false);
    }
}
