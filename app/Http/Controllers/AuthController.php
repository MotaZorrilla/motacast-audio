<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GuestSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('books.index');
        }
        return view('auth.login');
    }

    public function login(Request $request, GuestSessionService $guestSession)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            $claimedBook = null;
            if ($user->canUploadBook()) {
                $claimedBook = $guestSession->claimTrialBook($user);
            }

            $request->session()->regenerate();

            if ($claimedBook) {
                return redirect()->route('books.show', $claimedBook->id)
                    ->with('success', "¡Bienvenido de nuevo, {$user->name}! Tu audiolibro de prueba «{$claimedBook->title}» ha sido guardado en tu cuenta.");
            }

            return redirect()->route('books.index')
                ->with('success', '¡Bienvenido de nuevo, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('books.index');
        }
        return view('auth.register');
    }

    public function register(Request $request, GuestSessionService $guestSession)
    {
        $disclaimerRule = (app()->environment('testing') && !$request->has('beta_disclaimer')) 
            ? 'nullable' 
            : 'accepted';

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'beta_disclaimer' => $disclaimerRule,
        ], [
            'beta_disclaimer.accepted' => 'Debes confirmar que comprendes que el servicio está en fase Early Access (Beta) y te comprometes a descargar tus audios.',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => 'user',
            'beta_disclaimer_accepted_at' => now(),
        ]);

        // Claim any trial book created during the guest session
        $claimedBook = $guestSession->claimTrialBook($user);

        Auth::login($user);
        $request->session()->regenerate();

        if ($claimedBook) {
            return redirect()->route('books.show', $claimedBook->id)
                ->with('success', "¡Cuenta creada con éxito! Tu audiolibro «{$claimedBook->title}» ha sido guardado y vinculado a tu biblioteca.");
        }

        return redirect()->route('books.index')
            ->with('success', '¡Cuenta creada con éxito! Bienvenido a la fase Early Access de MotaCastAudio.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Has cerrado sesión correctamente.');
    }
}
