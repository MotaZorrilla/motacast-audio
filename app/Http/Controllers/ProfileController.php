<?php

namespace App\Http\Controllers;

use App\Services\AudioSynthesisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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

        $availableVoices = collect(AudioSynthesisService::getAvailableVoices())
            ->mapWithKeys(fn (array $v) => [$v['id'] => $v['profile_label'] ?? $v['name']])
            ->all();

        return view('profile.index', compact('user', 'totalBooks', 'recentBooks', 'totalDuration', 'totalWords', 'availableVoices'));
    }

    /**
     * Update user's profile information (name, email, preferred voice, preferred speed).
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $voiceIds = collect(AudioSynthesisService::getAvailableVoices())->pluck('id')->all();
        $speedRates = (array) config('audiolibros.speed_rates', ['-20%', '-10%', '+0%', '+10%', '+20%', '+30%']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'preferred_voice' => ['nullable', 'string', 'max:64', Rule::in($voiceIds)],
            'preferred_speed' => ['nullable', 'string', 'max:16', Rule::in($speedRates)],
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
