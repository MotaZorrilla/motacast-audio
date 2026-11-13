@extends('layouts.app')

@section('title', 'Mi Perfil & Ajustes - MotaCastAudio')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 sm:py-8 space-y-6 sm:space-y-8">
    
    <!-- Header Banner: Modern Deep Slate + Indigo Halo -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-slate-800 p-6 sm:p-8 shadow-xl">
        <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left">
            <!-- User Avatar with Indigo Gradient -->
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white text-3xl font-black shadow-lg ring-4 ring-indigo-500/20 flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            
            <div class="space-y-1.5 flex-1 min-w-0">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight truncate">
                        {{ $user->name }}
                    </h1>
                    @if($user->isAdmin())
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            ⭐ Administrador
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            👤 Podcaster / Oyente
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-300 font-medium">{{ $user->email }}</p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs text-slate-400 pt-1">
                    <span>🗓️ Miembro desde {{ $user->created_at->translatedFormat('F Y') }}</span>
                    <span>•</span>
                    <span class="inline-flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-emerald-400 font-semibold">Cuenta Activa</span>
                    </span>
                </div>
            </div>

            <!-- Quick Action Button -->
            <div class="flex-shrink-0 pt-2 sm:pt-0">
                <a href="{{ route('books.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800/90 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 shadow-sm transition active:scale-95">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Ir a mi Biblioteca</span>
                </a>
            </div>
        </div>

        <!-- Ambient decorative glow -->
        <div class="absolute -top-16 -right-16 w-64 h-64 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Stats & Quotas Grid (4-column responsive) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 sm:gap-4">
        <!-- Stat 1: Total Books -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Audiolibros Creados</p>
            <p class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ $totalBooks }}</p>
        </div>

        <!-- Stat 2: Quota Available -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Cupo Disponible</p>
            <p class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                @if($user->isAdmin() || $user->book_limit === -1)
                    <span class="text-emerald-500">Ilimitado</span>
                @else
                    {{ $user->remainingBooks() }} <span class="text-xs font-normal text-slate-400">/ {{ $user->book_limit }}</span>
                @endif
            </p>
        </div>

        <!-- Stat 3: Total Audio Duration -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-950/60 text-violet-600 dark:text-violet-400 flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Audio Generado</p>
            <p class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                @if(($totalDuration ?? 0) > 3600)
                    {{ number_format(($totalDuration ?? 0) / 3600, 1) }}h
                @elseif(($totalDuration ?? 0) > 0)
                    {{ round(($totalDuration ?? 0) / 60) }}m
                @else
                    0m
                @endif
            </p>
        </div>

        <!-- Stat 4: Total Words Converted -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Palabras Sintetizadas</p>
            <p class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                {{ number_format($totalWords ?? 0) }}
            </p>
        </div>
    </div>

    <!-- Forms Section: Profile Details & Audio Preferences / Password Security -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Form 1: Edit Profile & Audio Preferences -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
            <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
                <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7 7z" />
                    </svg>
                    <span>Datos Personales & Voz Preferida</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Configura tu identidad y tu voz favorita para conversiones automáticas.</p>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Nombre Completo
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}" 
                           required 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('name')
                        <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Correo Electrónico
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $user->email) }}" 
                           required 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('email')
                        <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Preferred Voice Default -->
                <div>
                    <label for="preferred_voice" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Voz Predeterminada para Conversión
                    </label>
                    <select id="preferred_voice" 
                            name="preferred_voice" 
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        @foreach(($availableVoices ?? []) as $vKey => $vLabel)
                            <option value="{{ $vKey }}" {{ old('preferred_voice', $user->preferred_voice ?? 'es-ES-AlvaroNeural') === $vKey ? 'selected' : '' }}>
                                {{ $vLabel }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Esta voz se preseleccionará al cargar tus nuevos documentos o audios.</p>
                </div>

                <!-- Preferred Speed Default -->
                <div>
                    <label for="preferred_speed" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Velocidad de Lectura Habitual
                    </label>
                    <select id="preferred_speed" 
                            name="preferred_speed" 
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <option value="-15%" {{ old('preferred_speed', $user->preferred_speed ?? '+0%') === '-15%' ? 'selected' : '' }}>Lenta (0.85x)</option>
                        <option value="+0%" {{ old('preferred_speed', $user->preferred_speed ?? '+0%') === '+0%' ? 'selected' : '' }}>Normal (1.0x - Recomendada)</option>
                        <option value="+15%" {{ old('preferred_speed', $user->preferred_speed ?? '+0%') === '+15%' ? 'selected' : '' }}>Dinámica (1.15x)</option>
                        <option value="+25%" {{ old('preferred_speed', $user->preferred_speed ?? '+0%') === '+25%' ? 'selected' : '' }}>Rápida (1.25x)</option>
                        <option value="+35%" {{ old('preferred_speed', $user->preferred_speed ?? '+0%') === '+35%' ? 'selected' : '' }}>Ultra Rápida (1.35x)</option>
                    </select>
                </div>

                <div class="pt-3">
                    <button type="submit" 
                            class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition active:scale-95">
                        Guardar Perfil & Preferencias
                    </button>
                </div>
            </form>
        </div>

        <!-- Form 2: Change Password & Security -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
            <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
                <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Seguridad & Contraseña</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Protege tu cuenta con una contraseña segura de al menos 6 caracteres.</p>
            </div>

            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Contraseña Actual
                    </label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           required 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('current_password')
                        <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Nueva Contraseña
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('password')
                        <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Confirmar Nueva Contraseña
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required 
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>

                <div class="pt-3">
                    <button type="submit" 
                            class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 shadow-sm transition active:scale-95">
                        Actualizar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Books Quick View -->
    @if($recentBooks->isNotEmpty())
        <div class="p-6 sm:p-7 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Tus Audiolibros Recientes</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Accede directamente al reproductor de tus últimas conversiones.</p>
                </div>
                <a href="{{ route('books.index') }}" class="text-xs font-bold text-indigo-500 hover:text-indigo-400 transition">
                    Ver todos →
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-2">
                @foreach($recentBooks as $b)
                    <a href="{{ route('books.show', $b) }}" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 hover:border-indigo-500/50 transition group flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center font-bold text-base flex-shrink-0 group-hover:scale-105 transition">
                            🎧
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-indigo-400 transition">{{ $b->title }}</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">{{ $b->created_at->diffForHumans() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
