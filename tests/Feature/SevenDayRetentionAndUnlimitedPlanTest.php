<?php

namespace Tests\Feature;

use App\Enums\BookStatus;
use App\Jobs\ProcessBookJob;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\User;
use App\Services\AudioSynthesisService;
use App\Services\PdfExtractorService;
use App\Services\TtsTextNormalizerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class SevenDayRetentionAndUnlimitedPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_registered_user_receives_unlimited_books_plan_with_retention_notice(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('conversiones ilimitadas', false)
            ->assertSee('7 días', false);

        $response = $this->post(route('register'), [
            'name' => 'Nuevo Usuario Ilimitado',
            'email' => 'ilimitado@ejemplo.com',
            'password' => 'SecretPass2026!',
            'password_confirmation' => 'SecretPass2026!',
            'beta_disclaimer' => '1',
        ]);

        $response->assertRedirect(route('books.index'));

        $user = User::where('email', 'ilimitado@ejemplo.com')->firstOrFail();
        $this->assertSame(-1, $user->book_limit);
        $this->assertTrue($user->canUploadBook());

        $this->actingAs($user)
            ->get(route('books.create'))
            ->assertOk()
            ->assertSee('Conversiones ilimitadas')
            ->assertSee('7 días');
    }

    public function test_unlimited_user_can_exceed_three_books_but_is_capped_at_two_concurrent_jobs(): void
    {
        Queue::fake();
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'user',
            'book_limit' => -1,
        ]);

        // 4 already completed books -> no problem on unlimited plan
        for ($i = 1; $i <= 4; $i++) {
            Book::create([
                'user_id' => $user->id,
                'title' => "Libro Listo {$i}",
                'original_filename' => "libro_{$i}.txt",
                'pdf_path' => "pdfs/libro_{$i}.txt",
                'status' => BookStatus::Ready->value,
            ]);
        }

        // 2 actively processing books -> fills the 2 concurrent job slots
        foreach (['pending', 'extracting'] as $idx => $state) {
            Book::create([
                'user_id' => $user->id,
                'title' => "En Cola {$idx}",
                'original_filename' => "cola_{$idx}.txt",
                'pdf_path' => "pdfs/cola_{$idx}.txt",
                'status' => $state,
            ]);
        }

        $blockedResponse = $this->actingAs($user)->post(route('books.store'), [
            'raw_text' => str_repeat('Texto válido suficiente para intentar encolar un tercer trabajo simultáneo. ', 3),
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $blockedResponse->assertRedirect(route('books.index'));
        $blockedResponse->assertSessionHas('error');

        // Once one finishes (ready), user can upload again freely beyond 4+ books
        $user->books()->where('status', BookStatus::Pending->value)->update(['status' => BookStatus::Ready->value]);

        $okResponse = $this->actingAs($user)->post(route('books.store'), [
            'pdf_file' => UploadedFile::fake()->create('quinto.txt', 5, 'text/plain'),
            'voice' => 'es-VE-SebastianNeural',
            'speed_rate' => '+0%',
            'pitch' => '+0Hz',
        ]);

        $okResponse->assertRedirect();
        $okResponse->assertSessionMissing('error');
        $this->assertSame(7, $user->books()->count());
    }

    public function test_process_book_job_assigns_seven_day_expiration_only_to_standard_users(): void
    {
        Storage::fake('public');

        $standardUser = User::factory()->create(['role' => 'user', 'book_limit' => -1]);
        $adminUser = User::factory()->create(['role' => 'admin', 'book_limit' => -1]);

        $userBook = Book::create([
            'user_id' => $standardUser->id,
            'title' => 'Transcripción Temporal 7D',
            'original_filename' => 'audio.m4a',
            'pdf_path' => 'pdfs/audio.m4a',
            'transcription_only' => true,
            'status' => 'pending',
        ]);

        $adminBook = Book::create([
            'user_id' => $adminUser->id,
            'title' => 'Archivo Permanente Admin',
            'original_filename' => 'admin.m4a',
            'pdf_path' => 'pdfs/admin.m4a',
            'transcription_only' => true,
            'status' => 'pending',
        ]);

        Storage::disk('public')->put($userBook->pdf_path, 'FAKE');
        Storage::disk('public')->put($adminBook->pdf_path, 'FAKE');

        $extractor = Mockery::mock(PdfExtractorService::class);
        $extractor->shouldReceive('extract')->twice()->andReturn([
            'success' => true,
            'title' => 'Transcripción',
            'author' => 'STT',
            'summary' => 'Resumen breve.',
            'total_words' => 5,
            'chapters' => [
                ['chapter_number' => 1, 'title' => 'Sección 1', 'text' => 'Texto transcrito válido.', 'word_count' => 3],
            ],
        ]);

        $synthesizer = Mockery::mock(AudioSynthesisService::class);
        $normalizer = new TtsTextNormalizerService;

        (new ProcessBookJob($userBook->id))->handle($extractor, $synthesizer, $normalizer);
        (new ProcessBookJob($adminBook->id))->handle($extractor, $synthesizer, $normalizer);

        $userBook->refresh();
        $adminBook->refresh();

        $this->assertNotNull($userBook->expires_at);
        $this->assertGreaterThanOrEqual(6, now()->diffInDays($userBook->expires_at));
        $this->assertNull($adminBook->expires_at);
    }

    public function test_purge_expired_command_deletes_only_expired_books_and_preserves_active_and_admin_books(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'user', 'book_limit' => -1]);
        $admin = User::factory()->create(['role' => 'admin', 'book_limit' => -1]);

        $expiredBook = Book::create([
            'user_id' => $user->id,
            'title' => 'Libro Vencido',
            'original_filename' => 'vencido.pdf',
            'pdf_path' => 'pdfs/vencido.pdf',
            'status' => 'ready',
            'expires_at' => now()->subHour(),
        ]);
        Storage::disk('public')->put('pdfs/vencido.pdf', 'OLD_PDF');
        Storage::disk('public')->put("audiobooks/{$expiredBook->id}/chapter_1.mp3", 'OLD_MP3');
        Chapter::create([
            'book_id' => $expiredBook->id,
            'chapter_number' => 1,
            'title' => 'Cap 1',
            'content_text' => 'Texto',
            'audio_path' => "audiobooks/{$expiredBook->id}/chapter_1.mp3",
            'status' => 'ready',
        ]);

        $activeBook = Book::create([
            'user_id' => $user->id,
            'title' => 'Libro Vigente',
            'original_filename' => 'vigente.pdf',
            'pdf_path' => 'pdfs/vigente.pdf',
            'status' => 'ready',
            'expires_at' => now()->addDays(5),
        ]);
        Storage::disk('public')->put('pdfs/vigente.pdf', 'ACTIVE_PDF');

        $adminBook = Book::create([
            'user_id' => $admin->id,
            'title' => 'Libro Permanente Admin',
            'original_filename' => 'admin_perm.pdf',
            'pdf_path' => 'pdfs/admin_perm.pdf',
            'status' => 'ready',
            'expires_at' => null,
        ]);
        Storage::disk('public')->put('pdfs/admin_perm.pdf', 'ADMIN_PDF');

        $this->artisan('audiolibros:purge-expired')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('books', ['id' => $expiredBook->id]);
        $this->assertDatabaseMissing('chapters', ['book_id' => $expiredBook->id]);
        Storage::disk('public')->assertMissing('pdfs/vencido.pdf');
        Storage::disk('public')->assertMissing("audiobooks/{$expiredBook->id}/chapter_1.mp3");

        $this->assertDatabaseHas('books', ['id' => $activeBook->id]);
        Storage::disk('public')->assertExists('pdfs/vigente.pdf');

        $this->assertDatabaseHas('books', ['id' => $adminBook->id]);
        Storage::disk('public')->assertExists('pdfs/admin_perm.pdf');
    }
}
