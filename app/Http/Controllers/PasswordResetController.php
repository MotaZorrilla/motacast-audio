<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm()
    {
        if (Auth::check()) {
            return redirect()->route('books.index');
        }

        return view('auth.forgot-password');
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Por favor ingresa tu correo electrónico.',
            'email.email' => 'El formato del correo electrónico no es válido.',
        ]);

        $email = Str::lower($request->input('email'));
        $throttleKey = 'password-reset-request:'.Str::transliterate($email.'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 4)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Has solicitado demasiados enlaces de restablecimiento recientemente. Por favor espera {$seconds} segundos antes de solicitar uno nuevo.",
            ])->onlyInput('email');
        }

        RateLimiter::hit($throttleKey, 180);

        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'No encontramos ninguna cuenta registrada con este correo electrónico. Por favor verifica que esté bien escrito.',
            ])->onlyInput('email');
        }

        if ($user->isSuspended()) {
            return back()->withErrors([
                'email' => 'Esta cuenta se encuentra suspendida. Por favor contacta a soporte para reactivar tu acceso.',
            ])->onlyInput('email');
        }

        // Generate reset token and dispatch localized notification
        $token = Password::broker()->createToken($user);
        $user->sendPasswordResetNotification($token);

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        // In Early Access / Homelab (or local testing where mailer may be log),
        // flash the direct link to the session so the user is never stranded
        session()->flash('beta_reset_url', $resetUrl);

        return back()->with('status', 'Hemos generado el enlace de restablecimiento para tu cuenta. Revisa tu buzón de correo o utiliza el acceso directo a continuación.');
    }

    /**
     * Display the password reset view for the given token.
     */
    public function showResetForm(Request $request, string $token)
    {
        if (Auth::check()) {
            return redirect()->route('books.index');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', old('email', '')),
        ]);
    }

    /**
     * Reset the given user's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'token.required' => 'El token de seguridad es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'password.required' => 'Debes ingresar una nueva contraseña.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $email = Str::lower($request->input('email'));
        $throttleKey = 'password-reset-submit:'.Str::transliterate($email.'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Demasiados intentos fallidos. Por favor espera {$seconds} segundos antes de volver a intentar.",
            ])->onlyInput('email');
        }

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'status' => 'active',
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            RateLimiter::clear($throttleKey);

            $user = User::where('email', $email)->first();

            if ($user) {
                Auth::login($user);
                $request->session()->regenerate();

                return redirect()->route('books.index')
                    ->with('success', '¡Tu contraseña ha sido restablecida con éxito! Has ingresado automáticamente a tu biblioteca.');
            }

            return redirect()->route('login')
                ->with('success', 'Tu contraseña ha sido restablecida con éxito. Ya puedes iniciar sesión con tu nueva clave.');
        }

        RateLimiter::hit($throttleKey, 300);

        $errorMessage = match ($status) {
            Password::INVALID_USER => 'No encontramos ninguna cuenta registrada con este correo electrónico.',
            Password::INVALID_TOKEN => 'El enlace o token de restablecimiento es inválido o ya ha expirado. Por favor solicita uno nuevo.',
            Password::RESET_THROTTLED => 'Por favor espera antes de volver a solicitar un cambio de contraseña.',
            default => 'Ocurrió un error al restablecer tu contraseña. Por favor intenta de nuevo.',
        };

        return back()->withErrors([
            'email' => $errorMessage,
        ])->onlyInput('email');
    }
}
