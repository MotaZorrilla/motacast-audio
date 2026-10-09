<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PasswordResetWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login:francisco@test.com|127.0.0.1');
        RateLimiter::clear('password-reset-request:francisco@test.com|127.0.0.1');
        RateLimiter::clear('password-reset-submit:francisco@test.com|127.0.0.1');
    }

    public function test_login_page_renders_visible_forgot_password_link(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee(route('password.request'));
        $response->assertSee('¿Olvidaste tu contraseña?');
    }

    public function test_forgot_password_page_renders_cleanly(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Recuperar Acceso', false);
        $response->assertSee('Generar Enlace de Recuperación');
        $response->assertSee(route('password.email'));
    }

    public function test_user_can_request_password_reset_link_and_receives_token(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'francisco@test.com',
            'name' => 'Francisco Pinango',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'francisco@test.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $response->assertSessionHas('beta_reset_url');

        Notification::assertSentTo($user, ResetPasswordNotification::class);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'francisco@test.com',
        ]);
    }

    public function test_non_existent_email_request_returns_clear_error(): void
    {
        $response = $this->post(route('password.email'), [
            'email' => 'desconocido@ejemplo.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_forgot_password_rate_limiting_prevents_spam(): void
    {
        $user = User::factory()->create([
            'email' => 'francisco@test.com',
        ]);

        for ($i = 0; $i < 4; $i++) {
            $this->post(route('password.email'), ['email' => 'francisco@test.com']);
        }

        $response = $this->post(route('password.email'), ['email' => 'francisco@test.com']);
        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('demasiados enlaces', session('errors')->first('email'));
    }

    public function test_user_can_view_reset_password_page_with_token(): void
    {
        $user = User::factory()->create([
            'email' => 'francisco@test.com',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->get(route('password.reset', [
            'token' => $token,
            'email' => 'francisco@test.com',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Nueva Contraseña');
        $response->assertSee('francisco@test.com');
        $response->assertSee($token);
    }

    public function test_user_can_reset_password_and_is_logged_in(): void
    {
        $user = User::factory()->create([
            'email' => 'francisco@test.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'francisco@test.com',
            'password' => 'NewPassword2026!',
            'password_confirmation' => 'NewPassword2026!',
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertAuthenticatedAs($user);

        $this->assertTrue(Hash::check('NewPassword2026!', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'francisco@test.com',
        ]);
    }

    public function test_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'francisco@test.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->post(route('password.update'), [
            'token' => 'invalid-token-string',
            'email' => 'francisco@test.com',
            'password' => 'NewPassword2026!',
            'password_confirmation' => 'NewPassword2026!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));
    }

    public function test_login_rate_limiting_locks_out_after_failed_attempts_with_recovery_message(): void
    {
        $user = User::factory()->create([
            'email' => 'francisco@test.com',
            'password' => Hash::make('ValidPassword123!'),
        ]);

        // Fail 5 times
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email' => 'francisco@test.com',
                'password' => 'WrongPassword!',
            ]);
        }

        // 6th attempt should be blocked by RateLimiter
        $response = $this->post(route('login'), [
            'email' => 'francisco@test.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $errorMessage = session('errors')->first('email');
        $this->assertStringContainsString('Demasiados intentos fallidos', $errorMessage);
        $this->assertStringContainsString('¿Olvidaste tu contraseña?', $errorMessage);
    }
}
