@extends('layouts.app')

@section('title', 'Restablecer Contraseña - MotaCastAudio')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 py-8 relative z-10">
    <div class="w-full max-w-md space-y-6">
        
        <!-- Brand Header with Bespoke Squircle Icon -->
        <div class="text-center space-y-3">
            <div class="inline-flex relative group">
                <img src="{{ asset('images/motacast-icon.svg') }}" 
                     alt="MotaCastAudio" 
                     class="w-16 h-16 rounded-2xl object-contain drop-shadow-[0_4px_20px_rgba(99,102,241,0.35)] group-hover:scale-105 transition transform duration-300"
                     onerror="this.onerror=null; this.src='{{ asset('images/icono.png') }}';">
                <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-emerald-500 ring-4 ring-white dark:ring-[#090d16] animate-pulse"></span>
            </div>
            
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                    Nueva <span class="text-indigo-600 dark:text-indigo-400 drop-shadow-[0_0_10px_rgba(99,102,241,0.3)]">Contraseña</span>
                </h1>
                <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">
                    Define tus nuevas credenciales de acceso seguro
                </p>
            </div>
        </div>

        <!-- Glassmorphism Tactile Reset Card -->
        <div class="card-tactile rounded-3xl p-6 sm:p-8 space-y-6 bg-white/95 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-xl shadow-black/30 backdrop-blur-xl">

            <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Hidden Reset Token -->
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email Input -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Correo Electrónico
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email', $email) }}" 
                            required 
                            autocomplete="email" 
                            placeholder="tu@correo.com"
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        >
                    </div>
                    @error('email')
                        <p class="text-[11px] font-semibold text-rose-500 dark:text-rose-400 mt-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- New Password Input -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Nueva Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            required 
                            autofocus
                            autocomplete="new-password"
                            placeholder="Mínimo 6 caracteres"
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        >
                    </div>
                    @error('password')
                        <p class="text-[11px] font-semibold text-rose-500 dark:text-rose-400 mt-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Confirm New Password Input -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Confirmar Nueva Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            id="password_confirmation" 
                            required 
                            autocomplete="new-password"
                            placeholder="Repite la nueva contraseña"
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        >
                    </div>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full btn-primary-tactile py-3 px-4 rounded-xl text-sm font-black text-white flex items-center justify-center gap-2 transition transform active:scale-95 shadow-lg shadow-indigo-500/25 mt-2"
                >
                    <span>Guardar y Entrar a MotaCastAudio</span>
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </form>

            <!-- Navigation Links -->
            <div class="text-center pt-3 border-t border-slate-200/80 dark:border-slate-800/80 text-xs">
                <a href="{{ route('login') }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition flex items-center justify-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Volver al Inicio de Sesión</span>
                </a>
            </div>

        </div>

    </div>
</div>
@endsection
