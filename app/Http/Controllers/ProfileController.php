<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Display the user's account settings and profile.
     */
    public function show()
    {
        $user = Auth::user();
        $totalBooks = $user->books()->count();
        $recentBooks = $user->books()->latest()->take(3)->get();
        $totalDuration = $user->books()->sum('total_duration');
        $totalWords = $user->books()->sum('total_words');

        $availableVoices = [
            'es-ES-AlvaroNeural' => 'Álvaro (España - Masculina, Dinámica y Natural)',
            'es-ES-ElviraNeural' => 'Elvira (España - Femenina, Clara y Expresiva)',
            'es-MX-JorgeNeural' => 'Jorge (México - Masculina, Cálida y Neutral)',
            'es-MX-DaliaNeural' => 'Dalia (México - Femenina, Dulce y Didáctica)',
            'es-VE-SebastianNeural' => 'Sebastián (Venezuela - Masculina, Sonora y Convincente)',
            'es-CO-GonzaloNeural' => 'Gonzalo (Colombia - Masculina, Académica)',
            'es-AR-TomasNeural' => 'Tomás (Argentina - Masculina, Urbana y Cercana)',
        ];

        return view('profile.index', compact('user', 'totalBooks', 'recentBooks', 'totalDuration', 'totalWords', 'availableVoices'));
    }

    /**
     * Update user's profile information (name, email, preferred voice, preferred speed).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'preferred_voice' => ['nullable', 'string', 'max:64'],
            'preferred_speed' => ['nullable', 'string', 'max:16'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado por otro usuario.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (isset($validated['preferred_voice'])) {
            $user->preferred_voice = $validated['preferred_voice'];
        }
        if (isset($validated['preferred_speed'])) {
            $user->preferred_speed = $validated['preferred_speed'];
        }
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Tu perfil y preferencias han sido actualizados correctamente.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Debes ingresar tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.required' => 'Debes ingresar una nueva contraseña.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Tu contraseña ha sido cambiada exitosamente.');
    }
}
