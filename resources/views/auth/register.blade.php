@extends('layouts.app')

@section('title', 'Crear Cuenta - MotaCastAudio')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 py-8 relative z-10">
    <div class="w-full max-w-md space-y-6">
        
        <!-- Brand Header with Bespoke Squircle Icon -->
        <div class="text-center space-y-3">
            <div class="inline-flex relative group">
                <img src="{{ asset('images/motacast-icon.svg') }}" 
                     alt="MotaCastAudio" 
                     class="w-16 h-16 rounded-2xl object-contain drop-shadow-[0_4px_20px_rgba(0,255,135,0.45)] group-hover:scale-105 transition transform duration-300"
                     onerror="this.onerror=null; this.src='{{ asset('images/icono.png') }}';">
                <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-[#00ff87] ring-4 ring-white dark:ring-[#070b0d] animate-pulse"></span>
            </div>
            
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-cyan-200">
                    Crear Cuenta <span class="text-[#00c965] dark:text-[#00ff87] drop-shadow-[0_0_10px_rgba(0,255,135,0.4)]">Personal</span>
                </h1>
                <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-sky-300/80 mt-1">
                    Convierte tus PDFs técnicos en audiolibros y escúchalos en streaming
                </p>
            </div>
        </div>

        <!-- Glassmorphism Tactile Register Card -->
        <div class="card-tactile rounded-3xl p-6 sm:p-8 space-y-6">
            @if (session('guest_book_id'))
                <div class="p-3.5 rounded-2xl bg-emerald-500/10 dark:bg-emerald-950/40 border border-emerald-500/30 text-center space-y-2">
                    <p class="text-xs text-slate-700 dark:text-cyan-200 font-medium">
                        ¿Deseas seguir escuchando tu audiolibro antes de crear tu cuenta?
                    </p>
                    <a href="{{ route('books.show', session('guest_book_id')) }}" class="btn-neon-tactile inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition transform active:scale-95 shadow-sm">
                        <svg class="w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Volver a mi Audiolibro de Prueba</span>
                    </a>
                    <div class="pt-1.5 flex items-center justify-center gap-3">
                        <a href="{{ route('guest.reset') }}" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline inline-flex items-center gap-1 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Reiniciar prueba para cargar otro documento</span>
                        </a>
                    </div>
                </div>
            @elseif (session('guest_upload_count'))
                <div class="p-3.5 rounded-2xl bg-amber-500/10 dark:bg-amber-950/40 border border-amber-500/30 text-center space-y-2">
                    <p class="text-xs text-slate-700 dark:text-cyan-200 font-medium">
                        ¿Deseas probar con otro documento antes de registrarte?
                    </p>
                    <a href="{{ route('guest.reset') }}" class="btn-neon-tactile inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition transform active:scale-95 shadow-sm">
                        <span>↺ Reiniciar y Cargar Otro Documento</span>
                    </a>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Name Input -->
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">
                        Nombre Completo
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500/70">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            value="{{ old('name') }}" 
                            required 
                            autofocus
                            placeholder="Ej. Héctor Mota"
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50/80 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/60 rounded-xl text-slate-900 dark:text-cyan-100 placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-[#00ff87] focus:border-transparent transition"
                        >
                    </div>
                </div>

                <!-- Email Input -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">
                        Correo Electrónico
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500/70">
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
                            placeholder="tu@correo.com"
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50/80 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/60 rounded-xl text-slate-900 dark:text-cyan-100 placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-[#00ff87] focus:border-transparent transition"
                        >
                    </div>
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">
                        Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500/70">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            required 
                            autocomplete="new-password"
                            placeholder="Mínimo 6 caracteres"
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50/80 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/60 rounded-xl text-slate-900 dark:text-cyan-100 placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-[#00ff87] focus:border-transparent transition"
                        >
                    </div>
                </div>

                <!-- Confirm Password Input -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">
                        Confirmar Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-cyan-500/70">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            name="password_confirmation" 
                            id="password_confirmation" 
                            required 
                            placeholder="Repite la contraseña"
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50/80 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/60 rounded-xl text-slate-900 dark:text-cyan-100 placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-[#00ff87] focus:border-transparent transition"
                        >
                    </div>
                </div>

                <!-- Master Neon Tactile Submit Button -->
                <button 
                    type="submit" 
                    class="w-full btn-neon-tactile py-3 px-4 rounded-xl text-sm font-black text-slate-950 flex items-center justify-center gap-2 transition transform active:scale-95 shadow-neon-md mt-3"
                >
                    <span>Registrarme y Comenzar</span>
                    <svg class="w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <!-- Login Navigation Callout -->
            <div class="text-center pt-3 border-t border-slate-200/80 dark:border-cyan-950/60 text-xs text-slate-500 dark:text-sky-300">
                ¿Ya tienes una cuenta? 
                <a href="{{ route('login') }}" class="font-bold text-[#00c965] dark:text-[#00ff87] hover:underline transition ml-1">
                    Inicia sesión aquí
                </a>
            </div>

        </div>

    </div>
</div>
@endsection
