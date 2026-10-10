@extends('layouts.app')

@section('title', 'Mis Documentos Hablados - MotaCastAudio')

@section('content')
<div class="space-y-6">

    <!-- Top Hero & Stats Card: Sleek Podcast Library Header -->
    <div class="card-tactile rounded-3xl p-5 sm:p-7 relative overflow-hidden transition-all">
        <!-- Subtle Indigo ambient glow -->
        <div class="absolute -top-16 -right-16 w-56 h-56 bg-indigo-500/10 dark:bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-indigo-500/10 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Streaming Activo
                    </span>
                    @auth
                        @if (!Auth::user()->isAdmin())
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                Cuota: <strong class="text-slate-900 dark:text-white">{{ Auth::user()->books()->count() }}</strong> / {{ Auth::user()->book_limit === -1 ? '∞' : Auth::user()->book_limit }} libros
                            </span>
                        @endif
                    @endauth
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Mis Documentos & Podcasts</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Tu biblioteca personal de audiolibros, lecturas y notas narradas con voces neuronales.
                </p>
            </div>
            
            <a href="{{ route('books.create') }}" class="btn-primary-tactile inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm font-black transition flex-shrink-0 shadow-lg shadow-indigo-500/20 active:scale-95">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Nuevo Documento</span>
            </a>
        </div>

        <!-- Metrics Row -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mt-6 pt-5 border-t border-slate-200/80 dark:border-slate-800/80 relative z-10">
            <div class="bg-slate-50 dark:bg-slate-950/60 rounded-2xl p-3.5 border border-slate-200/80 dark:border-slate-800/60 shadow-inner">
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Documentos</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-950/60 rounded-2xl p-3.5 border border-slate-200/80 dark:border-slate-800/60 shadow-inner">
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Listos para Escuchar</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $stats['ready'] }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-950/60 rounded-2xl p-3.5 border border-slate-200/80 dark:border-slate-800/60 shadow-inner">
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">En Síntesis</p>
                <p class="text-2xl font-black text-amber-500 dark:text-amber-400 mt-0.5">{{ $stats['processing'] }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-950/60 rounded-2xl p-3.5 border border-slate-200/80 dark:border-slate-800/60 shadow-inner">
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Horas de Audio</p>
                <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $stats['total_hours'] }}h</p>
            </div>
        </div>

        <!-- Admin Global Scope & User Filtering Toolbar -->
        @auth
            @if (Auth::user()->isAdmin())
                <div class="mt-4 pt-4 border-t border-slate-200/80 dark:border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            <span>Filtro por Usuario:</span>
                        </span>
                        
                        <form action="{{ route('books.index') }}" method="GET" class="inline-block" id="adminUserFilterForm">
                            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif

                            <select 
                                name="user_id" 
                                onchange="document.getElementById('adminUserFilterForm').submit()"
                                class="px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-200 font-medium focus:ring-2 focus:ring-indigo-500 outline-none"
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
                            <span class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-mono text-[11px] border border-indigo-200 dark:border-indigo-800">
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
                        <p class="text-[11px] sm:text-xs text-slate-600 dark:text-slate-400 mt-0.5 leading-relaxed">
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
                        <button type="submit" class="btn-primary-tactile px-3.5 py-2 rounded-xl text-xs font-black shadow-md transition">
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

    <!-- Filter & Search Bar: Tactile & Thumb-Friendly -->
    <div class="card-tactile rounded-2xl p-3 sm:p-4 flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('books.index') }}" method="GET" class="w-full md:w-auto flex-grow max-w-md">
            @if(request('user_id')) <input type="hidden" name="user_id" value="{{ request('user_id') }}"> @endif
            @if(request('scope')) <input type="hidden" name="scope" value="{{ request('scope') }}"> @endif
            <div class="relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Buscar documento por título o autor..."
                    class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                >
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </form>

        <!-- Status Filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-0.5">
            <a href="{{ route('books.index', array_merge(request()->except('status'))) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap {{ !request('status') || request('status') === 'all' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'bg-slate-100 dark:bg-slate-950 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }} transition">
                Todos ({{ $stats['total'] }})
            </a>
            <a href="{{ route('books.index', array_merge(request()->except('status'), ['status' => 'ready'])) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap {{ request('status') === 'ready' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-slate-100 dark:bg-slate-950 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }} transition">
                Listos ({{ $stats['ready'] }})
            </a>
            <a href="{{ route('books.index', array_merge(request()->except('status'), ['status' => 'synthesizing'])) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap {{ in_array(request('status'), ['synthesizing', 'extracting', 'pending']) ? 'bg-amber-500 text-slate-950' : 'bg-slate-100 dark:bg-slate-950 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }} transition">
                En Proceso ({{ $stats['processing'] }})
            </a>
        </div>
    </div>

    <!-- Podcast / Audiobook Cards Grid -->
    @if ($books->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach ($books as $book)
                <div class="card-tactile rounded-3xl overflow-hidden hover:border-indigo-500/40 transition-all flex flex-col group shadow-sm">
                    
                    <!-- Card Header & Cover Artwork -->
                    <div class="p-5 pb-3 flex items-start gap-3.5">
                        <div class="w-14 h-16 sm:w-16 sm:h-20 rounded-2xl bg-gradient-to-br from-indigo-600 via-violet-600 to-indigo-900 text-white flex flex-col items-center justify-center flex-shrink-0 shadow-md group-hover:scale-105 transition">
                            <span class="text-2xl">🎧</span>
                            <span class="text-[8px] font-black font-mono tracking-wider mt-1 uppercase text-indigo-200">EPISODIO</span>
                        </div>
                        <div class="min-w-0 flex-grow">
                            <div class="flex items-center gap-1.5 mb-1.5 flex-wrap">
                                @if ($book->status === 'ready')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Listo
                                    </span>
                                @elseif ($book->isProcessing())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {{ $book->progress_percentage }}%
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Error
                                    </span>
                                @endif

                                @if (Auth::user() && Auth::user()->isAdmin())
                                    @if ($book->user)
                                        <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 truncate max-w-[120px]" title="Cargado por {{ $book->user->name }}">
                                            👤 {{ $book->user->name }}
                                        </span>
                                    @else
                                        <span class="text-[10px] px-2 py-0.5 rounded-md font-mono font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 truncate max-w-[160px]" title="{{ $book->guest_fingerprint ?? 'Invitado Anónimo' }}">
                                            {{ $book->guest_fingerprint ? Str::limit($book->guest_fingerprint, 22) : '👤 Invitado' }}
                                        </span>
                                    @endif
                                @endif
                            </div>

                            <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white group-hover:text-indigo-400 transition truncate" title="{{ $book->title }}">
                                <a href="{{ route('books.show', $book->id) }}">{{ $book->title }}</a>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                {{ $book->author ?: 'Documento personal' }}
                            </p>
                        </div>
                    </div>

                    <!-- Meta Tags: Chapters & Total Audio Duration -->
                    <div class="px-5 py-2.5 flex items-center justify-between text-xs text-slate-600 dark:text-slate-300 border-t border-b border-slate-200/80 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-950/50">
                        <span class="font-bold flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            <span>{{ $book->chapters_count }} Capítulos</span>
                        </span>
                        <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $book->formatted_duration }}</span>
                    </div>

                    <!-- Executive Summary Excerpt -->
                    @if ($book->summary)
                        <div class="px-5 py-3 bg-slate-50/40 dark:bg-slate-950/30 border-b border-slate-200/60 dark:border-slate-800/60 text-xs">
                            <div class="summary-container" data-expanded="false">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider text-indigo-500">
                                        <span>⚡ Resumen</span>
                                    </span>
                                    @if ($book->summary_audio_path)
                                        <a href="{{ route('books.show', $book->id) }}?play=summary" class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition" title="Escuchar audio del resumen">
                                            <svg class="w-3 h-3 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Escuchar</span>
                                        </a>
                                    @endif
                                </div>
                                <p class="summary-text text-slate-600 dark:text-slate-300 leading-relaxed line-clamp-2 transition-all">
                                    {{ $book->summary }}
                                </p>
                                @if (mb_strlen($book->summary) > 110)
                                    <button type="button" onclick="toggleSummary(this)" class="mt-1 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1 transition">
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
                        <div class="px-5 pt-3">
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-500 shadow-sm" style="width: {{ $book->progress_percentage }}%"></div>
                            </div>
                        </div>
                    @endif

                    <!-- Card Action Footer -->
                    <div class="p-5 pt-3 mt-auto flex items-center justify-between gap-2.5">
                        <a href="{{ route('books.show', $book->id) }}" class="flex-grow btn-primary-tactile inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black transition active:scale-95 shadow-sm">
                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ $book->status === 'ready' ? 'Escuchar' : 'Ver Progreso' }}</span>
                        </a>

                        <!-- In-App Book Reader Quick Launch Link -->
                        <a href="{{ route('books.show', $book->id) }}#read" class="p-2.5 text-slate-500 dark:text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-slate-800 rounded-xl transition" title="Leer Documento en pantalla">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </a>

                        @auth
                            <form action="{{ route('books.destroy', $book->id) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar este documento de tu biblioteca?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition" title="Eliminar documento">
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
        <div class="card-tactile rounded-3xl p-8 sm:p-12 text-center max-w-lg mx-auto my-8">
            <div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-3xl mx-auto mb-4 shadow-sm">
                🎧
            </div>
            <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">Aún no tienes documentos en tu biblioteca</h3>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                Sube tu primer archivo PDF, Word o notas para sintetizarlo en capítulos de podcast con voces neuronales.
            </p>
            <div class="mt-6">
                <a href="{{ route('books.create') }}" class="btn-primary-tactile inline-flex items-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm font-black transition active:scale-95 shadow-md">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Cargar Primer Documento</span>
                </a>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
    function toggleSummary(btn) {
        const container = btn.closest('.summary-container');
        const text = container.querySelector('.summary-text');
        const chevron = btn.querySelector('.chevron');
        const label = btn.querySelector('.btn-label');
        const isExpanded = container.dataset.expanded === 'true';

        if (isExpanded) {
            text.classList.add('line-clamp-2');
            chevron.classList.remove('rotate-180');
            label.textContent = 'Ver más';
            container.dataset.expanded = 'false';
        } else {
            text.classList.remove('line-clamp-2');
            chevron.classList.add('rotate-180');
            label.textContent = 'Ver menos';
            container.dataset.expanded = 'true';
        }
    }
</script>
@endpush
@endsection
