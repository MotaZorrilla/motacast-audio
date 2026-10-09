@extends('layouts.app')

@section('title', 'Mis Documentos Hablados - MotaCastAudio')

@section('content')
<div class="space-y-5">

    <!-- Top Hero & Stats Card (Tactile 3D in Day Mode, Cyber Glow in Dark Mode) -->
    <div class="card-tactile rounded-2xl p-5 sm:p-6 relative overflow-hidden transition-all">
        <!-- Neon decorative glow -->
        <div class="absolute -top-16 -right-16 w-56 h-56 bg-[#00ff87]/10 dark:bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#00ff87]/15 dark:bg-cyan-950/40 text-[#00c965] dark:text-cyan-300 border border-[#00ff87]/30 dark:border-cyan-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00ff87] dark:bg-cyan-400 animate-pulse"></span> Streaming Activo
                    </span>
                    @auth
                        @if (!Auth::user()->isAdmin())
                            <span class="text-xs text-slate-500 dark:text-sky-300 font-mono">
                                Cuota: <strong class="text-slate-900 dark:text-cyan-200">{{ Auth::user()->books()->count() }}</strong> / {{ Auth::user()->book_limit === -1 ? '∞' : Auth::user()->book_limit }} libros
                            </span>
                        @endif
                    @endauth
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-cyan-200 tracking-tight">Mis Documentos & Libros</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-sky-300/80 mt-0.5">
                    Escucha tus lecturas técnicas, libros y artículos con voz natural dondequiera que estés.
                </p>
            </div>
            
            <a href="{{ route('books.create') }}" class="btn-neon-tactile inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-black transition flex-shrink-0">
                <svg class="w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Agregar Libro / PDF</span>
            </a>
        </div>

        <!-- Orchid Software KPI Metrics Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5 pt-5 border-t border-slate-200/80 dark:border-cyan-950/60 relative z-10">
            <div class="card-tactile rounded-2xl p-3.5 sm:p-4 flex items-center justify-between shadow-xs">
                <div>
                    <p class="text-[10px] font-mono font-bold text-slate-500 dark:text-sky-400 uppercase tracking-wider">Documentos</p>
                    <p class="text-2xl font-black text-slate-900 dark:text-cyan-300 mt-0.5">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-cyan-950/60 flex items-center justify-center text-slate-600 dark:text-cyan-400 border border-slate-200/60 dark:border-cyan-900/40">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>

            <div class="card-tactile rounded-2xl p-3.5 sm:p-4 flex items-center justify-between shadow-xs border-emerald-500/30">
                <div>
                    <p class="text-[10px] font-mono font-bold text-slate-500 dark:text-sky-400 uppercase tracking-wider">Listos</p>
                    <p class="text-2xl font-black text-[#00c965] dark:text-[#00ff87] mt-0.5">{{ $stats['ready'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#00ff87]/15 flex items-center justify-center text-[#00c965] dark:text-[#00ff87] border border-[#00ff87]/30">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <div class="card-tactile rounded-2xl p-3.5 sm:p-4 flex items-center justify-between shadow-xs border-amber-500/30">
                <div>
                    <p class="text-[10px] font-mono font-bold text-slate-500 dark:text-sky-400 uppercase tracking-wider">En Síntesis</p>
                    <p class="text-2xl font-black text-amber-500 dark:text-amber-400 mt-0.5">{{ $stats['processing'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center text-amber-500 border border-amber-500/30">
                    <svg class="w-5 h-5 {{ $stats['processing'] > 0 ? 'animate-spin' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
            </div>

            <div class="card-tactile rounded-2xl p-3.5 sm:p-4 flex items-center justify-between shadow-xs border-cyan-500/30">
                <div>
                    <p class="text-[10px] font-mono font-bold text-slate-500 dark:text-sky-400 uppercase tracking-wider">Horas de Audio</p>
                    <p class="text-2xl font-black text-cyan-600 dark:text-cyan-400 mt-0.5">{{ $stats['total_hours'] }}h</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-cyan-500/15 flex items-center justify-center text-cyan-500 border border-cyan-500/30">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Admin Global Scope & User Filtering Toolbar -->
        @auth
            @if (Auth::user()->isAdmin())
                <div class="mt-4 pt-4 border-t border-slate-200/80 dark:border-cyan-950/60 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-700 dark:text-cyan-300 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                            <span>Filtro por Usuario:</span>
                        </span>
                        
                        <form action="{{ route('books.index') }}" method="GET" class="inline-block" id="adminUserFilterForm">
                            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif

                            <select 
                                name="user_id" 
                                onchange="document.getElementById('adminUserFilterForm').submit()"
                                class="px-2.5 py-1 text-xs bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-800/60 rounded-lg text-slate-900 dark:text-cyan-200 font-medium focus:ring-2 focus:ring-[#00ff87] outline-none"
                            >
                                <option value="all" {{ empty($selectedUserId) && request('scope') !== 'mine' ? 'selected' : '' }}>Todos los usuarios (Global)</option>
                                <option value="mine" {{ request('scope') === 'mine' ? 'selected' : '' }}>Solo mis documentos (Admin)</option>
                                <option value="guests" {{ $selectedUserId === 'guests' ? 'selected' : '' }}>👤 Solo libros de Invitados Anónimos</option>
                                <optgroup label="Filtrar por cuenta específica:">
                                    @foreach ($usersList as $u)
                                        <option value="{{ $u->id }}" {{ $selectedUserId === $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->email }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </form>
                    </div>

                    @if ($selectedUserId)
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 font-mono text-[11px] border border-cyan-300 dark:border-cyan-800">
                                @if ($selectedUserId === 'guests')
                                    Mostrando libros de Invitados Anónimos
                                @else
                                    Mostrando libros del usuario #{{ $selectedUserId }}
                                @endif
                            </span>
                            <a href="{{ route('books.index') }}" class="text-rose-500 hover:underline font-semibold text-[11px]">Quitar filtro</a>
                        </div>
                    @endif
                </div>
            @endif
        @endauth
    </div>

    <!-- Quota Limit & Semi-Automatic Extension Banner for Users -->
    @auth
        @if (!Auth::user()->isAdmin() && !Auth::user()->canUploadBook())
            <div class="card-tactile rounded-2xl p-4 sm:p-5 border border-amber-500/40 bg-amber-500/10 dark:bg-amber-950/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-md">
                <div class="flex items-start sm:items-center gap-3">
                    <span class="p-2.5 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-amber-200">
                            Has alcanzado el límite de tu cuenta ({{ Auth::user()->books()->count() }} de {{ Auth::user()->book_limit }} libros)
                        </h3>
                        <p class="text-[11px] sm:text-xs text-slate-600 dark:text-sky-300/80 mt-0.5 leading-relaxed">
                            @if (Auth::user()->canRequestAutoExtension())
                                Como usuario de fase Beta, puedes activar una <strong>extensión de cortesía inmediata (+1 libro)</strong> con un solo toque.
                            @else
                                Ya utilizaste tu cortesía inicial. Puedes solicitar una ampliación adicional personalizada al Administrador.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <form action="{{ route('tickets.request-extension') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-neon-tactile px-3.5 py-2 rounded-xl text-xs font-black shadow-md transition">
                            @if (Auth::user()->canRequestAutoExtension())
                                ⚡ Activar Cortesía (+1 Libro)
                            @else
                                📩 Solicitar Ampliación
                            @endif
                        </button>
                    </form>
                    <button type="button" onclick="openSupportModal()" class="px-3 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                        Escribir Nota
                    </button>
                </div>
            </div>
        @endif
    @endauth

    <!-- Filter & Search Bar -->
    <div class="card-tactile rounded-xl p-3 flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('books.index') }}" method="GET" class="w-full md:w-auto flex-grow max-w-md">
            @if(request('user_id')) <input type="hidden" name="user_id" value="{{ request('user_id') }}"> @endif
            @if(request('scope')) <input type="hidden" name="scope" value="{{ request('scope') }}"> @endif
            <div class="relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Buscar documento por título o autor..."
                    class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/50 rounded-xl text-slate-900 dark:text-cyan-200 placeholder-slate-400 dark:placeholder-sky-500/50 focus:outline-none focus:ring-2 focus:ring-[#00ff87] transition"
                >
                <svg class="w-4 h-4 text-slate-400 dark:text-cyan-500 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </form>

        <!-- Status Filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-0.5">
            <a href="{{ route('books.index', array_merge(request()->except('status'))) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap {{ !request('status') || request('status') === 'all' ? 'bg-[#00ff87] text-slate-950 shadow-sm shadow-[#00ff87]/30' : 'bg-slate-100 dark:bg-[#071014] text-slate-600 dark:text-sky-300 hover:dark:text-cyan-300' }} transition">
                Todos
            </a>
            <a href="{{ route('books.index', array_merge(request()->except('status'), ['status' => 'ready'])) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap {{ request('status') === 'ready' ? 'bg-[#00ff87] text-slate-950 shadow-sm shadow-[#00ff87]/30' : 'bg-slate-100 dark:bg-[#071014] text-slate-600 dark:text-sky-300 hover:dark:text-cyan-300' }} transition">
                Listos
            </a>
            <a href="{{ route('books.index', array_merge(request()->except('status'), ['status' => 'synthesizing'])) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap {{ in_array(request('status'), ['synthesizing', 'extracting', 'pending']) ? 'bg-amber-500 text-slate-950' : 'bg-slate-100 dark:bg-[#071014] text-slate-600 dark:text-sky-300 hover:dark:text-cyan-300' }} transition">
                En Proceso
            </a>
        </div>
    </div>

    <!-- Books Card Grid -->
    @if ($books->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach ($books as $book)
                <div class="card-tactile rounded-2xl overflow-hidden hover:border-[#00ff87]/50 dark:hover:border-cyan-400/50 transition-all flex flex-col group">
                    
                    <!-- Card Top Area -->
                    <div class="p-4 sm:p-5 pb-3 flex items-start gap-3.5">
                        <div class="w-12 h-16 sm:w-14 sm:h-18 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-100 dark:from-[#081a17] dark:to-[#042621] border border-emerald-300 dark:border-cyan-500/30 flex flex-col items-center justify-center text-emerald-700 dark:text-cyan-400 flex-shrink-0 shadow-inner group-hover:scale-105 transition">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span class="text-[8px] font-black font-mono tracking-wider mt-0.5 uppercase">PDF</span>
                        </div>
                        <div class="min-w-0 flex-grow">
                            <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                                @if ($book->status === 'ready')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#00ff87]/15 text-[#00c965] dark:text-[#00ff87] border border-[#00ff87]/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#00ff87]"></span> Listo
                                    </span>
                                @elseif ($book->isProcessing())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {{ $book->progress_percentage }}%
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Error
                                    </span>
                                @endif

                                @if (Auth::user() && Auth::user()->isAdmin())
                                    @if ($book->user)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-cyan-950/40 text-slate-600 dark:text-cyan-300 border border-transparent dark:border-cyan-900/40 truncate max-w-[120px]" title="Cargado por {{ $book->user->name }}">
                                            👤 {{ $book->user->name }}
                                        </span>
                                    @else
                                        <span class="text-[10px] px-1.5 py-0.5 rounded font-mono font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 truncate max-w-[160px]" title="{{ $book->guest_fingerprint ?? 'Invitado Anónimo' }}">
                                            {{ $book->guest_fingerprint ? Str::limit($book->guest_fingerprint, 22) : '👤 Invitado' }}
                                        </span>
                                    @endif
                                @endif
                            </div>

                            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-cyan-200 group-hover:text-emerald-500 dark:group-hover:text-cyan-400 transition truncate" title="{{ $book->title }}">
                                <a href="{{ route('books.show', $book->id) }}">{{ $book->title }}</a>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-sky-300/80 truncate mt-0.5">
                                {{ $book->author ?: 'Documento personal' }}
                            </p>
                        </div>
                    </div>

                    <!-- Meta Tags -->
                    <div class="px-4 py-2 flex items-center justify-between text-xs text-slate-600 dark:text-sky-300 border-t border-b border-slate-200/80 dark:border-cyan-950/60 bg-slate-50/70 dark:bg-[#071014]/60">
                        <span class="font-semibold">{{ $book->chapters_count }} Pistas</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-cyan-300">{{ $book->formatted_duration }}</span>
                    </div>

                    <!-- Executive Summary Excerpt (if available) -->
                    @if ($book->summary)
                        <div class="px-4 py-2.5 bg-slate-50/50 dark:bg-[#071014]/40 border-b border-slate-200/70 dark:border-cyan-950/50 text-xs">
                            <div class="summary-container" data-expanded="false">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider text-[#00c965] dark:text-[#00ff87]">
                                        <span>⚡ Resumen</span>
                                    </span>
                                    @if ($book->summary_audio_path)
                                        <a href="{{ route('books.show', $book->id) }}?play=summary" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-cyan-400 hover:text-[#00ff87] transition" title="Escuchar audio del resumen">
                                            <svg class="w-3 h-3 text-[#00c965] dark:text-[#00ff87]" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Escuchar</span>
                                        </a>
                                    @endif
                                </div>
                                <p class="summary-text text-slate-600 dark:text-sky-200/80 leading-relaxed line-clamp-2 transition-all">
                                    {{ $book->summary }}
                                </p>
                                @if (mb_strlen($book->summary) > 110)
                                    <button type="button" onclick="toggleSummary(this)" class="mt-1 text-[11px] font-bold text-emerald-600 dark:text-[#00ff87] hover:underline inline-flex items-center gap-1 transition">
                                        <span class="btn-label">Ver más</span>
                                        <svg class="w-3 h-3 chevron transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Processing Progress Bar -->
                    @if ($book->isProcessing())
                        <div class="px-4 pt-2.5">
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-[#00ff87] h-1.5 rounded-full transition-all duration-500 shadow-neon-sm" style="width: {{ $book->progress_percentage }}%"></div>
                            </div>
                        </div>
                    @endif

                    <!-- Card Action Footer -->
                    <div class="p-4 pt-3 mt-auto flex items-center justify-between gap-2">
                        <a href="{{ route('books.show', $book->id) }}" class="flex-grow btn-neon-tactile inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-black transition">
                            <svg class="w-3.5 h-3.5 text-slate-950" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $book->status === 'ready' ? 'Escuchar' : 'Ver Progreso' }}</span>
                        </a>

                        <!-- In-App Book Reader Quick Launch Link -->
                        <a href="{{ route('books.show', $book->id) }}#read" class="p-2 text-slate-500 dark:text-cyan-400 hover:text-emerald-500 hover:bg-emerald-50 dark:hover:bg-cyan-950/40 rounded-xl transition" title="Leer Libro en visor integrado">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </a>

                        @auth
                            <form id="delete-form-{{ $book->id }}" action="{{ route('books.destroy', $book->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDeleteDocument('{{ $book->id }}', '{{ addslashes($book->title) }}')" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition min-w-[36px] min-h-[36px] flex items-center justify-center" title="Eliminar documento">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        @endauth
                    </div>

                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $books->links() }}
        </div>

    @else
        <!-- Empty State -->
        <div class="card-tactile rounded-2xl p-8 sm:p-12 text-center max-w-lg mx-auto my-6">
            <div class="w-14 h-14 rounded-2xl bg-[#00ff87]/15 dark:bg-cyan-950/50 border border-[#00ff87]/30 dark:border-cyan-500/30 flex items-center justify-center text-[#00c965] dark:text-cyan-400 mx-auto mb-3 shadow-neon-sm">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-cyan-200">Aún no hay documentos en esta vista</h3>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-sky-300 mt-1 max-w-sm mx-auto">
                Sube tu primer archivo PDF para sintetizarlo en audio y disfrutar de la experiencia streaming.
            </p>
            <div class="mt-5">
                <a href="{{ route('books.create') }}" class="btn-neon-tactile inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-black transition">
                    <svg class="w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Cargar Primer PDF</span>
                </a>
            </div>
        </div>
    @endif

    <!-- Non-Blocking Delete Confirmation Modal (Protocolo 4 Craft Floor) -->
    <div id="deleteDocModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="card-tactile w-full max-w-sm rounded-3xl p-6 text-center space-y-4 shadow-2xl">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/15 text-rose-500 mx-auto flex items-center justify-center border border-rose-500/20">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-cyan-200">¿Eliminar este documento?</h3>
                <p id="deleteDocModalTitle" class="text-xs text-slate-600 dark:text-sky-300 font-semibold mt-1 truncate max-w-xs mx-auto"></p>
                <p class="text-[11px] text-slate-500 dark:text-sky-400 mt-1">Esta acción borrará el audio y el contenido del audiolibro.</p>
            </div>
            <div class="flex items-center justify-center gap-2 pt-2">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition min-h-[44px]">
                    Cancelar
                </button>
                <button type="button" id="confirmDeleteSubmitBtn" class="px-5 py-2.5 rounded-xl text-xs font-black bg-rose-500 hover:bg-rose-600 text-white shadow-md transition min-h-[44px]">
                    Sí, Eliminar
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    let deleteTargetDocId = null;
    function confirmDeleteDocument(id, title) {
        deleteTargetDocId = id;
        const titleEl = document.getElementById('deleteDocModalTitle');
        if (titleEl) titleEl.textContent = title;
        document.getElementById('deleteDocModal').classList.remove('hidden');
    }
    function closeDeleteModal() {
        deleteTargetDocId = null;
        document.getElementById('deleteDocModal').classList.add('hidden');
    }
    document.getElementById('confirmDeleteSubmitBtn')?.addEventListener('click', () => {
        if (deleteTargetDocId) {
            const form = document.getElementById('delete-form-' + deleteTargetDocId);
            if (form) form.submit();
        }
    });
</script>
@endpush
@endsection
