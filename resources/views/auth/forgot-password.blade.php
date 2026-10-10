@extends('layouts.app')

@section('title', 'Recuperar Contraseña - MotaCastAudio')

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
                    Recuperar Acceso
                </h1>
                <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">
                    Ingresa tu correo para restablecer tu contraseña de forma autónoma
                </p>
            </div>
        </div>

        <!-- Glassmorphism Tactile Recovery Card -->
        <div class="card-tactile rounded-3xl p-6 sm:p-8 space-y-6 bg-white/95 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-xl shadow-black/30 backdrop-blur-xl">

            <!-- Early Access Instant Recovery Banner (Zero-Delay Self-Service) -->
            @if (session('beta_reset_url'))
                <div class="p-4 rounded-2xl bg-indigo-500/10 dark:bg-indigo-950/50 border border-indigo-500/40 text-center space-y-3 shadow-lg shadow-indigo-500/10">
                    <div class="flex items-center justify-center gap-2 text-indigo-600 dark:text-indigo-400 font-bold text-xs uppercase tracking-wider">
                        <svg class="w-4 h-4 animate-bounce text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Enlace de Recuperación Generado</span>
                    </div>
                    <p class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-relaxed">
                        En esta fase de servicio puedes ingresar directamente mediante este acceso seguro de un solo uso:
                    </p>
                    <a href="{{ session('beta_reset_url') }}" class="btn-primary-tactile w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black text-white transition transform active:scale-95 shadow-md">
                        <span>Restablecer Mi Contraseña Ahora</span>
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Email Input -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Correo Electrónico Registrado
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
                            value="{{ old('email') }}" 
                            required 
                            autocomplete="email" 
                            autofocus
                            placeholder="francisco@ejemplo.com"
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

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full btn-primary-tactile py-3 px-4 rounded-xl text-sm font-black text-white flex items-center justify-center gap-2 transition transform active:scale-95 shadow-lg shadow-indigo-500/25 mt-2"
                >
                    <span>Generar Enlace de Recuperación</span>
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </button>
            </form>

            <!-- Navigation Links -->
            <div class="pt-3 border-t border-slate-200/80 dark:border-slate-800/80 flex flex-col items-center gap-2 text-xs">
                <a href="{{ route('login') }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Volver al Inicio de Sesión</span>
                </a>
                <div class="text-slate-500 dark:text-slate-400">
                    ¿No recuerdas tu cuenta? 
                    <a href="{{ route('register') }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline ml-1">
                        Crea una nueva aquí
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
