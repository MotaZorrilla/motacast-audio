<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_profile(): void
    {
        $response = $this->get(route('profile.show'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Hector Mota',
            'email' => 'hector@motazorrilla.com',
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));

        $response->assertOk();
        $response->assertSee('Hector Mota');
        $response->assertSee('hector@motazorrilla.com');
        $response->assertSee('Audiolibros Creados');
    }

    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@ejemplo.com',
            'preferred_voice' => 'es-ES-AlvaroNeural',
            'preferred_speed' => '+0%',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nuevo Nombre',
            'email' => 'nuevo@ejemplo.com',
            'preferred_voice' => 'es-ES-ElviraNeural',
            'preferred_speed' => '+15%',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Nuevo Nombre', $user->name);
        $this->assertEquals('nuevo@ejemplo.com', $user->email);
        $this->assertEquals('es-ES-ElviraNeural', $user->preferred_voice);
        $this->assertEquals('+15%', $user->preferred_speed);
    }

    public function test_user_cannot_update_to_existing_email(): void
    {
        User::factory()->create(['email' => 'otro@ejemplo.com']);
        $user = User::factory()->create(['email' => 'yo@ejemplo.com']);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Mi Nombre',
            'email' => 'otro@ejemplo.com',
        ]);

        $response->assertSessionHasErrors(['email']);
        $user->refresh();
        $this->assertEquals('yo@ejemplo.com', $user->email);
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'password123',
            'password' => 'nuevaClave456',
            'password_confirmation' => 'nuevaClave456',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('nuevaClave456', $user->password));
    }

    public function test_user_cannot_update_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'claveErronea',
            'password' => 'nuevaClave456',
            'password_confirmation' => 'nuevaClave456',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $user->refresh();
        $this->assertTrue(Hash::check('password123', $user->password));
    }
}
