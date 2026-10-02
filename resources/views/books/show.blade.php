@extends('layouts.app')

@php
    $cleanSummary = !empty($book->summary) ? trim(preg_replace('/\s+/', ' ', strip_tags($book->summary))) : '';
    $ogDescription = !empty($cleanSummary) 
        ? \Illuminate\Support\Str::limit($cleanSummary, 180, '...')
        : "Escucha '{$book->title}' de " . ($book->author ?: 'Documento personal') . " en formato audiolibro neuronal ({$book->chapters->count()} pistas, {$book->formatted_duration}). Generado con MotaCastAudio.";

    $ext = strtolower(pathinfo($book->original_filename ?? $book->pdf_path, PATHINFO_EXTENSION));
    $isImageUpload = in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'bmp']);
    $ogImage = ($isImageUpload && \Illuminate\Support\Facades\Storage::disk('public')->exists($book->pdf_path))
        ? asset('storage/' . $book->pdf_path)
        : asset('images/motacast-og-banner.jpg');
@endphp

@section('title', $book->title . ' - MotaCastAudio')
@section('meta_description', $ogDescription)
@section('meta_author', $book->author ?: 'MotaCastAudio')
@section('og_title', $book->title . ' - Audiolibro en MotaCastAudio')
@section('og_description', $ogDescription)
@section('og_type', 'book')
@section('og_url', route('books.show', $book->id))
@section('og_image', $ogImage)
@section('og_image_alt', 'Audiolibro: ' . $book->title)

@section('content')
<div class="space-y-4 pb-36 sm:pb-28">

    <!-- Top Navigation Breadcrumbs & Top Quick Actions -->
    <div class="flex items-center justify-between">
        @auth
            <a href="{{ route('books.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-emerald-500 dark:hover:text-[#00ff87] transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Volver a Mis Documentos</span>
            </a>
        @else
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold bg-[#00ff87]/10 text-emerald-700 dark:text-[#00ff87] border border-[#00ff87]/30">
                    <span class="w-2 h-2 rounded-full bg-[#00ff87] animate-pulse"></span>
                    <span>Libro de Prueba</span>
                </span>
                <a href="{{ route('books.create') }}" class="btn-neon-tactile px-2.5 py-1 rounded-xl text-[11px] font-black inline-flex items-center gap-1 shadow-sm" title="Cargar otro documento">
                    <svg class="w-3.5 h-3.5 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Cargar otro</span>
                </a>
                <a href="{{ route('guest.reset') }}" onclick="return confirm('¿Deseas reiniciar tu prueba gratuita para subir o pegar otro documento?');" class="px-2.5 py-1 text-amber-600 dark:text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 rounded-xl transition text-[11px] font-bold inline-flex items-center gap-1 shadow-sm" title="Reiniciar sesión de prueba">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Reiniciar</span>
                </a>
            </div>
        @endauth

        <div class="flex items-center gap-2">
            @auth
                <form action="{{ route('books.destroy', $book->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este documento de tu biblioteca?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition" title="Eliminar documento">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </form>
            @endauth
        </div>
    </div>

    <!-- Mobile-First Compact Header (<75px tall on Phones) -->
    <div class="block sm:hidden card-tactile rounded-2xl p-3">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <!-- Compact Neon Thumbnail -->
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#00ff87] to-emerald-700 text-slate-950 flex items-center justify-center flex-shrink-0 shadow-sm shadow-[#00ff87]/30">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xs font-black text-slate-900 dark:text-cyan-200 truncate leading-tight">{{ $book->title }}</h1>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-sky-400 mt-0.5">
                        <span class="truncate max-w-[100px]">{{ $book->author ?: 'Documento' }}</span>
                        <span>&bull;</span>
                        <span class="font-mono text-[#00c965] dark:text-[#00ff87] font-bold">{{ $book->formatted_duration }}</span>
                    </div>
                </div>
            </div>

            <!-- Mobile Quick Actions -->
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <button 
                    type="button" 
                    onclick="openPdfModal()"
                    class="btn-neon-tactile px-3 py-1.5 rounded-xl text-[11px] font-black flex items-center gap-1.5 shadow-sm"
                    title="Leer Documento"
                >
                    <svg class="w-3.5 h-3.5 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Leer</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Desktop Header Card (Full Spanning on sm+ screens) -->
    <div class="hidden sm:block card-tactile rounded-2xl p-5 sm:p-6 relative overflow-hidden">
        <!-- Neon decorative glow -->
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-[#00ff87]/10 dark:bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-center gap-5 relative z-10">
            <!-- Audio Cover Avatar with Neon Depth -->
            <div class="w-16 h-20 sm:w-20 sm:h-24 rounded-2xl bg-gradient-to-br from-[#10e86b] via-[#00ff87] to-emerald-800 text-slate-950 flex flex-col items-center justify-center shadow-neon-md flex-shrink-0 border border-white/30">
                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                </svg>
                <span class="text-[9px] font-black font-mono tracking-wider mt-1 uppercase">AUDIO</span>
            </div>

            <!-- Title & Metadata -->
            <div class="flex-grow space-y-1.5 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span id="bookStatusBadge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $book->status === 'ready' ? 'bg-[#00ff87]/15 text-[#00c965] dark:text-[#00ff87] border border-[#00ff87]/30' : ($book->hasFailed() ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800' : 'bg-amber-500/15 text-amber-500 border border-amber-500/30 animate-pulse') }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $book->status === 'ready' ? 'bg-[#00ff87]' : ($book->hasFailed() ? 'bg-rose-500' : 'bg-amber-500 animate-ping') }}"></span>
                        <span id="bookStatusText">
                            @if ($book->status === 'ready') Listo para Escuchar
                            @elseif ($book->status === 'extracting') Extrayendo texto...
                            @elseif ($book->status === 'synthesizing') Generando audio...
                            @elseif ($book->status === 'failed') Inconveniente en procesamiento
                            @else En Cola
                            @endif
                        </span>
                    </span>

                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-mono bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                        </svg>
                        <span>{{ $book->voice }}</span>
                    </span>

                    <span class="text-xs text-slate-400 dark:text-slate-500 font-mono truncate max-w-[200px]">
                        {{ $book->original_filename }}
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-cyan-200 tracking-tight truncate" id="bookTitle">
                        {{ $book->title }}
                    </h1>
                    <button 
                        type="button" 
                        onclick="openPdfModal()" 
                        class="btn-neon-tactile px-4 py-2 rounded-xl text-xs font-black flex items-center gap-2 flex-shrink-0 shadow-neon-sm hover:scale-105 active:scale-95 transition"
                        title="Leer Documento en Pantalla"
                    >
                        <svg class="w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>Leer Libro</span>
                    </button>
                </div>

                <p class="text-sm font-medium text-slate-600 dark:text-sky-300 truncate" id="bookAuthor">
                    {{ $book->author ?: 'Documento personal' }}
                </p>

                <!-- Stats Bar -->
                <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-slate-500 dark:text-sky-300/80 pt-1">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500 dark:text-[#00ff87]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span id="bookChaptersCount">{{ $book->chapters->count() }} Pistas</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500 dark:text-[#00ff87]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span id="bookDuration" class="font-bold text-slate-900 dark:text-cyan-300 font-mono">{{ $book->formatted_duration }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-400 dark:text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        <span>{{ number_format($book->total_words) }} palabras</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Status and Progress Banners (Modular Partial) --}}
    @include('books.partials.status-banners')

    {{-- Executive Summary Card (Modular Partial) --}}
    @include('books.partials.summary-card')

    <!-- Chapter Playlist Section -->
    <div class="card-tactile rounded-2xl overflow-hidden">
        <div class="p-3.5 sm:p-4 border-b border-slate-200 dark:border-cyan-950/60 bg-slate-50/70 dark:bg-[#071014]/60 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500 dark:text-[#00ff87]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-cyan-200">Pistas del Audiolibro</h2>
            </div>
            <span class="text-xs font-bold text-slate-500 dark:text-sky-400 font-mono" id="playlistCount">
                {{ $book->chapters->where('status', 'ready')->count() }} de {{ $book->chapters->count() }} listas
            </span>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-cyan-950/50" id="chaptersList">
            @forelse ($book->chapters as $index => $chapter)
                <div 
                    class="chapter-row p-3 sm:p-4 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-[#121c21] transition cursor-pointer group {{ $index === 0 && $chapter->status === 'ready' ? 'bg-[#00ff87]/5 dark:bg-[#00ff87]/10' : '' }}"
                    id="chapterRow-{{ $chapter->id }}"
                    data-id="{{ $chapter->id }}"
                    data-number="{{ $chapter->chapter_number }}"
                    data-title="{{ $chapter->title }}"
                    data-stream-url="{{ $chapter->audio_stream_url }}"
                    data-download-url="{{ $chapter->audio_download_url }}"
                    data-status="{{ $chapter->status }}"
                    data-duration="{{ $chapter->duration_seconds }}"
                    onclick="handleChapterClick({{ $chapter->id }})"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Play / Chapter Number Badge -->
                        <div class="chapter-play-btn w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 group-hover:bg-[#00ff87] group-hover:text-slate-950 flex items-center justify-center flex-shrink-0 transition shadow-sm font-bold" id="btnPlayChapter-{{ $chapter->id }}">
                            @if ($chapter->status === 'ready')
                                <svg class="w-4 h-4 ml-0.5 icon-play" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                </svg>
                                <svg class="w-4 h-4 icon-pause hidden" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            @elseif ($chapter->status === 'synthesizing')
                                <svg class="w-4 h-4 animate-spin text-[#00ff87]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            @else
                                <span class="text-xs font-mono font-bold">{{ $chapter->chapter_number }}</span>
                            @endif
                        </div>

                        <!-- Chapter Title and Word Count -->
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] font-mono font-bold text-slate-400 dark:text-slate-500">P{{ $chapter->chapter_number }}</span>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white group-hover:text-emerald-500 dark:group-hover:text-[#00ff87] transition truncate">
                                    {{ $chapter->title }}
                                </h3>
                            </div>
                            <div class="flex items-center gap-2 text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                <span>{{ number_format($chapter->word_count) }} palabras</span>
                                <span>&bull;</span>
                                <span class="font-mono text-slate-500 dark:text-slate-400 font-semibold">{{ $chapter->formatted_duration }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Controls (Download MP3) -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if ($chapter->status === 'ready')
                            <a 
                                href="{{ $chapter->audio_download_url }}" 
                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-[#00ff87]/20 text-slate-700 dark:text-slate-200 rounded-lg text-[10px] font-bold transition"
                                title="Descargar MP3"
                                onclick="event.stopPropagation();"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>MP3</span>
                            </a>
                        @elseif ($chapter->status === 'synthesizing')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#00ff87]/15 text-[#00c965] dark:text-[#00ff87] border border-[#00ff87]/30">
                                Generando...
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                En espera
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                    Aún no se han generado capítulos. El proceso está activo.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Sticky Radioactive Neon Player Bar -->
<div id="stickyPlayerBar" class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-[#080d0f]/95 backdrop-blur-md border-t border-slate-200 dark:border-cyan-950/70 shadow-[0_-8px_25px_rgba(0,0,0,0.12)] dark:shadow-none py-2 px-3 sm:px-8 z-40 pb-[max(0.5rem,env(safe-area-inset-bottom))]">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-1.5 sm:gap-4">
        
        <!-- Track Info & Mobile Quick Controls -->
        <div class="flex items-center justify-between w-full md:w-1/4 gap-2 min-w-0">
            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-gradient-to-br from-[#10e86b] to-emerald-800 text-slate-950 flex items-center justify-center flex-shrink-0 shadow-sm shadow-[#00ff87]/20">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-slate-900 dark:text-cyan-200 truncate leading-tight" id="playerChapterTitle">Selecciona una pista</p>
                    <p class="text-[10px] text-slate-500 dark:text-sky-400 truncate" id="playerBookTitle">{{ $book->title }}</p>
                </div>
            </div>

            <!-- Mobile-only inline speed & mute buttons -->
            <div class="flex md:hidden items-center gap-1.5 flex-shrink-0">
                <select id="playerPlaybackRateMobile" class="px-1.5 py-0.5 text-[11px] font-bold bg-slate-100 dark:bg-[#071014] text-slate-700 dark:text-cyan-300 rounded-lg border border-slate-300 dark:border-cyan-800/60 focus:ring-1 focus:ring-[#00ff87]">
                    <option value="0.75">0.75x</option>
                    <option value="1.0" selected>1.0x</option>
                    <option value="1.25">1.25x</option>
                    <option value="1.5">1.5x</option>
                    <option value="2.0">2.0x</option>
                </select>
                <button type="button" id="btnMuteToggleMobile" class="p-1 text-slate-500 dark:text-sky-400 hover:text-slate-900 dark:hover:text-cyan-300 transition" title="Silenciar">
                    <svg class="w-4 h-4 icon-vol-high" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                    <svg class="w-4 h-4 icon-vol-muted hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Center Controls: Prev, Rewind 15s, Play/Pause, Forward 15s, Next & Scrubber -->
        <div class="flex flex-col items-center gap-1 w-full md:w-2/4">
            <div class="flex items-center gap-3 sm:gap-5">
                <!-- Previous Chapter -->
                <button id="btnPrevChapter" class="p-1.5 text-slate-400 dark:text-sky-500/60 hover:text-slate-900 dark:hover:text-cyan-300 transition" title="Pista Anterior">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Rewind 15s -->
                <button id="btnRewind15" class="p-1.5 text-slate-600 dark:text-sky-300 hover:text-[#00ff87] dark:hover:text-cyan-300 transition" title="Retroceder 15s">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z" />
                    </svg>
                </button>

                <!-- Master Radioactive Play/Pause Button -->
                <button id="btnMasterPlay" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full btn-neon-tactile text-slate-950 flex items-center justify-center shadow-neon-md transition transform hover:scale-105 active:scale-95" title="Reproducir / Pausar">
                    <svg id="iconMasterPlay" class="w-5 h-5 sm:w-6 sm:h-6 ml-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                    </svg>
                    <svg id="iconMasterPause" class="w-5 h-5 sm:w-6 sm:h-6 hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </button>

                <!-- Forward 15s -->
                <button id="btnForward15" class="p-1.5 text-slate-600 dark:text-sky-300 hover:text-[#00ff87] dark:hover:text-cyan-300 transition" title="Adelantar 15s">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.933 12.8a1 1 0 000-1.6L6.6 7.2A1 1 0 005 8v8a1 1 0 001.6.8l5.333-4zM19.933 12.8a1 1 0 000-1.6l-5.333-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.333-4z" />
                    </svg>
                </button>

                <!-- Next Chapter -->
                <button id="btnNextChapter" class="p-1.5 text-slate-400 dark:text-sky-500/60 hover:text-slate-900 dark:hover:text-cyan-300 transition" title="Siguiente Pista">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Scrubber Timeline & Elapsed Time -->
            <div class="w-full flex items-center gap-2">
                <span id="playerCurrentTime" class="font-mono text-[10px] sm:text-[11px] text-slate-500 dark:text-sky-400 w-8 text-right font-semibold">00:00</span>
                <input 
                    type="range" 
                    id="playerScrubber" 
                    min="0" 
                    max="100" 
                    value="0" 
                    class="w-full h-1.5 bg-slate-200 dark:bg-slate-750 rounded-lg appearance-none cursor-pointer accent-[#00ff87] focus:outline-none"
                >
                <span id="playerDuration" class="font-mono text-[10px] sm:text-[11px] text-slate-500 dark:text-sky-400 w-8 font-semibold">00:00</span>
            </div>
        </div>

        <!-- Desktop Right Side: Speed Selector, Volume, Download -->
        <div class="hidden md:flex items-center justify-end gap-2.5 w-full md:w-1/4">
            <!-- Speed Switcher -->
            <select id="playerPlaybackRate" class="px-2 py-1 text-xs font-bold bg-slate-100 dark:bg-[#071014] hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-cyan-300 rounded-xl border border-slate-300 dark:border-cyan-800/60 focus:ring-1 focus:ring-[#00ff87] transition cursor-pointer">
                <option value="0.75">0.75x</option>
                <option value="1.0" selected>1.0x</option>
                <option value="1.25">1.25x</option>
                <option value="1.5">1.5x</option>
                <option value="2.0">2.0x</option>
            </select>

            <!-- Volume Mute Toggle -->
            <button id="btnMuteToggle" class="p-1.5 text-slate-500 dark:text-sky-400 hover:text-slate-900 dark:hover:text-cyan-300 transition" title="Silenciar">
                <svg id="iconVolumeHigh" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                </svg>
                <svg id="iconVolumeMuted" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                </svg>
            </button>

            <!-- Download Current Track -->
            <a id="btnPlayerDownload" href="#" class="p-1.5 text-slate-500 dark:text-sky-400 hover:text-[#00ff87] dark:hover:text-cyan-300 transition" title="Descargar MP3 de la pista actual">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
            </a>
        </div>

    </div>
</div>

{{-- Universal In-App Document Reader Modal (Modular Partial) --}}
@include('books.partials.reader-modal')

<!-- Hidden HTML5 Audio Element -->
<audio id="audioEngine" preload="metadata"></audio>

@endsection

@push('scripts')
<script src="{{ asset('vendor/pdfjs/pdf.min.js') }}"></script>
<script>
    // Configure PDF.js Worker
    if (window.pdfjsLib) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('vendor/pdfjs/pdf.worker.min.js') }}";
    }

    // Audio Player Engine & Sticky Bar UI references
    const audioEngine = document.getElementById('audioEngine');
    window.audioEngine = audioEngine;
    const btnMasterPlay = document.getElementById('btnMasterPlay');
    const iconMasterPlay = document.getElementById('iconMasterPlay');
    const iconMasterPause = document.getElementById('iconMasterPause');
    const playerScrubber = document.getElementById('playerScrubber');
    const playerCurrentTime = document.getElementById('playerCurrentTime');
    const playerDuration = document.getElementById('playerDuration');
    const playerPlaybackRate = document.getElementById('playerPlaybackRate');
    const playerChapterTitle = document.getElementById('playerChapterTitle');
    const btnPrevChapter = document.getElementById('btnPrevChapter');
    const btnNextChapter = document.getElementById('btnNextChapter');
    const btnRewind15 = document.getElementById('btnRewind15');
    const btnForward15 = document.getElementById('btnForward15');
    const btnMuteToggle = document.getElementById('btnMuteToggle');
    const iconVolumeHigh = document.getElementById('iconVolumeHigh');
    const iconVolumeMuted = document.getElementById('iconVolumeMuted');
    const btnPlayerDownload = document.getElementById('btnPlayerDownload');

    // Modal Audio Console UI references
    const modalChapterTitle = document.getElementById('modalChapterTitle');
    const btnModalMasterPlay = document.getElementById('btnModalMasterPlay');
    const iconModalPlay = document.getElementById('iconModalPlay');
    const iconModalPause = document.getElementById('iconModalPause');
    const modalScrubber = document.getElementById('modalScrubber');
    const modalCurrentTime = document.getElementById('modalCurrentTime');
    const modalDuration = document.getElementById('modalDuration');
    const modalPlaybackRate = document.getElementById('modalPlaybackRate');
    const btnModalPrevChapter = document.getElementById('btnModalPrevChapter');
    const btnModalNextChapter = document.getElementById('btnModalNextChapter');
    const btnModalRewind15 = document.getElementById('btnModalRewind15');
    const btnModalForward15 = document.getElementById('btnModalForward15');
    const btnModalMuteToggle = document.getElementById('btnModalMuteToggle');
    const iconModalVolumeHigh = document.getElementById('iconModalVolumeHigh');
    const iconModalVolumeMuted = document.getElementById('iconModalVolumeMuted');
    const btnModalDownload = document.getElementById('btnModalDownload');

    let currentChapterId = null;
    let chaptersData = @json($book->chapters);
    let bookStatus = "{{ $book->status }}";
    const bookId = {{ $book->id }};
    const streamBaseTemplate = @json(route('chapters.stream', ['chapter' => '__ID__']));
    const downloadBaseTemplate = @json(route('chapters.download', ['chapter' => '__ID__']));
    const statusUrl = @json(route('books.status', $book->id));

    function formatTime(seconds) {
        if (isNaN(seconds) || seconds < 0) return '00:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    // Universal In-App Reader State & Engine
    const bookFormat = @json(strtolower(pathinfo($book->original_filename ?? $book->pdf_path, PATHINFO_EXTENSION))) || 'pdf';
    const isDocPdf = (bookFormat === 'pdf');
    let readerViewMode = isDocPdf ? 'canvas' : 'text'; // 'canvas' | 'text'
    let readerFontSize = parseInt(localStorage.getItem('motacast_reader_font_size') || '15', 10);
    let activeReaderChapter = 1;
    let pdfDoc = null;
    let pageNum = 1;
    let pageRendering = false;
    let pageNumPending = null;
    let pdfZoomLevel = 1.0;
    const pdfUrl = "{{ route('books.pdf', $book->id) }}";

    function setReaderViewMode(mode) {
        readerViewMode = mode;
        const canvasWrapper = document.getElementById('pdfCanvasWrapper');
        const textWrapper = document.getElementById('documentTextWrapper');
        const pdfPagination = document.getElementById('pdfPaginationBar');
        const textPagination = document.getElementById('textPaginationBar');
        const btnCanvas = document.getElementById('btnModeCanvas');
        const btnText = document.getElementById('btnModeText');
        const grpPdfZoom = document.getElementById('grpPdfZoomControls');
        const grpTextFont = document.getElementById('grpTextFontControls');
        const drawerTitle = document.getElementById('drawerTitleText');
        const quickJumpBox = document.getElementById('drawerQuickJumpBox');
        const drawerToggleText = document.getElementById('lblDrawerToggleText');

        if (mode === 'canvas' && isDocPdf) {
            if (canvasWrapper) { canvasWrapper.classList.remove('hidden'); canvasWrapper.classList.add('flex'); }
            if (textWrapper) { textWrapper.classList.add('hidden'); textWrapper.classList.remove('block'); }
            if (pdfPagination) { pdfPagination.classList.remove('hidden'); pdfPagination.classList.add('flex'); }
            if (textPagination) { textPagination.classList.add('hidden'); textPagination.classList.remove('flex'); }

            if (grpPdfZoom) grpPdfZoom.classList.remove('hidden');
            if (grpTextFont) grpTextFont.classList.add('hidden');
            if (quickJumpBox) quickJumpBox.classList.remove('hidden');

            if (btnCanvas) {
                btnCanvas.className = "px-2 py-0.5 text-[10px] font-bold rounded-lg bg-white dark:bg-[#071014] text-emerald-600 dark:text-[#00ff87] shadow-sm";
            }
            if (btnText) {
                btnText.className = "px-2 py-0.5 text-[10px] font-bold rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-cyan-200";
            }
            if (drawerTitle) drawerTitle.innerHTML = '<svg class="w-4 h-4 text-[#00ff87]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg><span>Miniaturas de Páginas</span>';
            if (drawerToggleText) drawerToggleText.textContent = 'Miniaturas';

            if (pdfDoc) {
                populateThumbnails(pdfDoc.numPages);
                renderPage(pageNum);
            }
        } else {
            // Text mode
            if (canvasWrapper) { canvasWrapper.classList.add('hidden'); canvasWrapper.classList.remove('flex'); }
            if (textWrapper) { textWrapper.classList.remove('hidden'); textWrapper.classList.add('block'); }
            if (pdfPagination) { pdfPagination.classList.add('hidden'); pdfPagination.classList.remove('flex'); }
            if (textPagination) { textPagination.classList.remove('hidden'); textPagination.classList.add('flex'); }

            if (grpPdfZoom) grpPdfZoom.classList.add('hidden');
            if (grpTextFont) grpTextFont.classList.remove('hidden');
            if (quickJumpBox) quickJumpBox.classList.add('hidden');

            if (btnCanvas) {
                btnCanvas.className = "px-2 py-0.5 text-[10px] font-bold rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-cyan-200";
            }
            if (btnText) {
                btnText.className = "px-2 py-0.5 text-[10px] font-bold rounded-lg bg-white dark:bg-[#071014] text-emerald-600 dark:text-[#00ff87] shadow-sm";
            }
            if (drawerTitle) drawerTitle.innerHTML = '<svg class="w-4 h-4 text-[#00ff87]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg><span>Índice de Capítulos</span>';
            if (drawerToggleText) drawerToggleText.textContent = 'Capítulos';

            populateChapterDrawer();
            applyReaderFontSize();
        }
    }

    function adjustReaderFontSize(delta) {
        readerFontSize = Math.max(12, Math.min(24, readerFontSize + delta));
        localStorage.setItem('motacast_reader_font_size', readerFontSize);
        applyReaderFontSize();
    }

    function applyReaderFontSize() {
        const lbl = document.getElementById('lblReaderFontSize');
        if (lbl) lbl.textContent = `${readerFontSize}px`;
        document.querySelectorAll('.reader-chapter-body').forEach(el => {
            el.style.fontSize = `${readerFontSize}px`;
        });
    }

    function populateChapterDrawer() {
        const container = document.getElementById('pdfThumbnailsContainer');
        const badge = document.getElementById('badgeThumbnailsCount');
        if (badge) badge.textContent = `(${chaptersData.length})`;
        if (!container) return;

        let html = '';
        chaptersData.forEach((ch, idx) => {
            const chapNum = ch.chapter_number || (idx + 1);
            html += `
                <button 
                    type="button" 
                    id="drawerChapItem-${chapNum}" 
                    onclick="jumpToReaderChapter(${chapNum})" 
                    class="w-full text-left p-2 rounded-xl flex items-center justify-between border transition text-xs ${chapNum === activeReaderChapter ? 'bg-[#00ff87]/15 border-[#00ff87] text-[#00c965] dark:text-[#00ff87]' : 'bg-white dark:bg-[#0c181d] border-slate-200 dark:border-cyan-900/40 text-slate-700 dark:text-cyan-300 hover:border-[#00ff87]/50'}"
                >
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-6 h-6 rounded-lg ${chapNum === activeReaderChapter ? 'bg-[#00ff87] text-slate-950 font-black' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold'} flex items-center justify-center font-mono text-[10px] flex-shrink-0">${chapNum}</span>
                        <div class="truncate">
                            <p class="font-medium truncate leading-tight">${escapeHtml(ch.title)}</p>
                            <span class="text-[10px] text-slate-400 font-mono">${ch.duration_seconds > 0 ? formatTime(ch.duration_seconds) : ''}</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400 flex-shrink-0">Leer →</span>
                </button>
            `;
        });
        container.innerHTML = html;
    }

    function jumpToReaderChapter(chapNumber) {
        activeReaderChapter = chapNumber;
        const target = document.getElementById(`readerChapSection-${chapNumber}`);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            target.classList.add('ring-2', 'ring-[#00ff87]');
            setTimeout(() => target.classList.remove('ring-2', 'ring-[#00ff87]'), 2000);
        }
        const lblNum = document.getElementById('lblActiveReaderChapNum');
        if (lblNum) lblNum.textContent = chapNumber;
        highlightActiveChapterDrawer(chapNumber);
    }

    function jumpToReaderAdjacentChapter(delta) {
        const next = Math.max(1, Math.min(chaptersData.length, activeReaderChapter + delta));
        jumpToReaderChapter(next);
    }

    function highlightActiveChapterDrawer(num) {
        chaptersData.forEach((ch, idx) => {
            const chapNum = ch.chapter_number || (idx + 1);
            const btn = document.getElementById(`drawerChapItem-${chapNum}`);
            if (btn) {
                if (chapNum === num) {
                    btn.className = "w-full text-left p-2 rounded-xl flex items-center justify-between border transition text-xs bg-[#00ff87]/15 border-[#00ff87] text-[#00c965] dark:text-[#00ff87]";
                } else {
                    btn.className = "w-full text-left p-2 rounded-xl flex items-center justify-between border transition text-xs bg-white dark:bg-[#0c181d] border-slate-200 dark:border-cyan-900/40 text-slate-700 dark:text-cyan-300 hover:border-[#00ff87]/50";
                }
            }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderPage(num) {
        if (!pdfDoc) return;
        pageRendering = true;
        const canvas = document.getElementById('pdfCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const loading = document.getElementById('pdfLoadingIndicator');
        if (loading) loading.classList.remove('hidden');

        pdfDoc.getPage(num).then(function(page) {
            const wrapper = document.getElementById('pdfCanvasWrapper');
            let availableWidth = (wrapper ? wrapper.clientWidth : window.innerWidth) - 36;
            if (availableWidth > 900) availableWidth = 900;
            if (availableWidth < 280) availableWidth = 280;

            const unscaledViewport = page.getViewport({ scale: 1.0 });
            const autoFitScale = (availableWidth / unscaledViewport.width) * pdfZoomLevel;
            const viewport = page.getViewport({ scale: autoFitScale });

            // HiDPI Retina razor-sharp text scaling
            const dpr = window.devicePixelRatio || 1;
            canvas.width = Math.floor(viewport.width * dpr);
            canvas.height = Math.floor(viewport.height * dpr);
            canvas.style.width = Math.floor(viewport.width) + 'px';
            canvas.style.height = Math.floor(viewport.height) + 'px';

            const transform = dpr !== 1 ? [dpr, 0, 0, dpr, 0, 0] : null;

            const renderContext = {
                canvasContext: ctx,
                transform: transform,
                viewport: viewport
            };

            const renderTask = page.render(renderContext);
            renderTask.promise.then(function() {
                pageRendering = false;
                if (loading) loading.classList.add('hidden');
                if (pageNumPending !== null) {
                    renderPage(pageNumPending);
                    pageNumPending = null;
                }
            });
        }).catch(function(err) {
            console.error('Error al renderizar página:', err);
            pageRendering = false;
            if (loading) loading.classList.add('hidden');
        });

        // Sync inputs & thumbnails
        const inputPage = document.getElementById('pdfCurrentPageInput');
        if (inputPage) inputPage.value = num;
        highlightActiveThumbnail(num);
        updatePdfNavButtons();
    }

    function queueRenderPage(num) {
        if (pageRendering) {
            pageNumPending = num;
        } else {
            renderPage(num);
        }
    }

    function onPdfPrevPage() {
        if (pageNum <= 1) return;
        pageNum--;
        queueRenderPage(pageNum);
    }

    function onPdfNextPage() {
        if (!pdfDoc || pageNum >= pdfDoc.numPages) return;
        pageNum++;
        queueRenderPage(pageNum);
    }

    function goToPdfPage(num) {
        if (!pdfDoc) return;
        num = Math.max(1, Math.min(pdfDoc.numPages, parseInt(num) || 1));
        pageNum = num;
        queueRenderPage(pageNum);
    }

    function goToLastPdfPage() {
        if (!pdfDoc) return;
        goToPdfPage(pdfDoc.numPages);
    }

    function handlePageInputChange(val) {
        goToPdfPage(val);
    }

    function handleQuickJump() {
        const input = document.getElementById('quickPageJumpInput');
        if (input && input.value) {
            goToPdfPage(input.value);
            input.value = '';
        }
    }

    function toggleThumbnailsDrawer() {
        const drawer = document.getElementById('pdfThumbnailsDrawer');
        if (drawer) {
            drawer.classList.toggle('hidden');
            drawer.classList.toggle('flex');
        }
    }

    function populateThumbnails(numPages) {
        const container = document.getElementById('pdfThumbnailsContainer');
        const badge = document.getElementById('badgeThumbnailsCount');
        if (badge) badge.textContent = `(${numPages})`;
        if (!container) return;
        
        let html = '';
        for (let i = 1; i <= numPages; i++) {
            html += `
                <button 
                    type="button" 
                    id="thumbItem-${i}" 
                    onclick="goToPdfPage(${i})" 
                    class="w-full text-left p-2 rounded-xl flex items-center justify-between border transition text-xs ${i === pageNum ? 'bg-[#00ff87]/15 border-[#00ff87] text-[#00c965] dark:text-[#00ff87]' : 'bg-white dark:bg-[#0c181d] border-slate-200 dark:border-cyan-900/40 text-slate-700 dark:text-cyan-300 hover:border-[#00ff87]/50'}"
                >
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-6 h-6 rounded-lg ${i === pageNum ? 'bg-[#00ff87] text-slate-950 font-black' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold'} flex items-center justify-center font-mono text-[10px] flex-shrink-0">${i}</span>
                        <span class="font-medium truncate">Página ${i}</span>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400">Ver →</span>
                </button>
            `;
        }
        container.innerHTML = html;
    }

    function highlightActiveThumbnail(num) {
        document.querySelectorAll('#pdfThumbnailsContainer button').forEach(btn => {
            btn.classList.remove('bg-[#00ff87]/15', 'border-[#00ff87]', 'text-[#00c965]', 'dark:text-[#00ff87]');
            btn.classList.add('bg-white', 'dark:bg-[#0c181d]', 'border-slate-200', 'dark:border-cyan-900/40', 'text-slate-700', 'dark:text-cyan-300');
            const badge = btn.querySelector('span');
            if (badge) {
                badge.className = 'w-6 h-6 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold flex items-center justify-center font-mono text-[10px] flex-shrink-0';
            }
        });
        const activeBtn = document.getElementById(`thumbItem-${num}`);
        if (activeBtn) {
            activeBtn.classList.add('bg-[#00ff87]/15', 'border-[#00ff87]', 'text-[#00c965]', 'dark:text-[#00ff87]');
            activeBtn.classList.remove('bg-white', 'dark:bg-[#0c181d]');
            const badge = activeBtn.querySelector('span');
            if (badge) {
                badge.className = 'w-6 h-6 rounded-lg bg-[#00ff87] text-slate-950 font-black flex items-center justify-center font-mono text-[10px] flex-shrink-0';
            }
        }
    }

    function zoomPdf(delta) {
        pdfZoomLevel = Math.max(0.6, Math.min(2.5, pdfZoomLevel + delta));
        const lbl = document.getElementById('lblZoomLevel');
        if (lbl) lbl.textContent = `${Math.round(pdfZoomLevel * 100)}%`;
        queueRenderPage(pageNum);
    }

    function resetZoomPdf() {
        pdfZoomLevel = 1.0;
        const lbl = document.getElementById('lblZoomLevel');
        if (lbl) lbl.textContent = 'Ajustar';
        queueRenderPage(pageNum);
    }

    function updatePdfNavButtons() {
        const btnPrev = document.getElementById('btnPdfPrev');
        const btnNext = document.getElementById('btnPdfNext');
        const btnFirst = document.getElementById('btnPdfFirst');
        const btnLast = document.getElementById('btnPdfLast');
        if (btnPrev) btnPrev.disabled = (pageNum <= 1);
        if (btnFirst) btnFirst.disabled = (pageNum <= 1);
        if (btnNext) btnNext.disabled = (!pdfDoc || pageNum >= pdfDoc.numPages);
        if (btnLast) btnLast.disabled = (!pdfDoc || pageNum >= pdfDoc.numPages);
    }

    function openPdfModal() {
        const modal = document.getElementById('pdfViewerModal');
        modal.classList.remove('hidden');

        if (!isDocPdf) {
            setReaderViewMode('text');
            return;
        }

        if (readerViewMode === 'canvas') {
            if (!pdfDoc && window.pdfjsLib) {
                const loading = document.getElementById('pdfLoadingIndicator');
                if (loading) loading.classList.remove('hidden');

                pdfjsLib.getDocument(pdfUrl).promise.then(function(pdfDoc_) {
                    pdfDoc = pdfDoc_;
                    document.getElementById('pdfTotalPages').textContent = pdfDoc.numPages;
                    const quickInput = document.getElementById('quickPageJumpInput');
                    if (quickInput) quickInput.max = pdfDoc.numPages;
                    populateThumbnails(pdfDoc.numPages);
                    renderPage(pageNum);
                }).catch(function(err) {
                    console.error('Error cargando documento PDF:', err);
                    if (loading) {
                        loading.innerHTML = '<div class="text-center p-4"><p class="text-rose-500 font-bold text-xs mb-2">No se pudo cargar en visor PDF. Mostrando texto extraído...</p></div>';
                        setTimeout(() => setReaderViewMode('text'), 1000);
                    }
                });
            } else if (pdfDoc) {
                renderPage(pageNum);
            }
        } else {
            setReaderViewMode('text');
        }
    }

    function closePdfModal() {
        document.getElementById('pdfViewerModal').classList.add('hidden');
    }

    // Keyboard shortcuts for PDF reader
    window.addEventListener('keydown', (e) => {
        const modal = document.getElementById('pdfViewerModal');
        if (modal && !modal.classList.contains('hidden')) {
            if (e.key === 'ArrowRight') onPdfNextPage();
            if (e.key === 'ArrowLeft') onPdfPrevPage();
            if (e.key === 'Escape') closePdfModal();
        }
    });

    // Auto-select first ready chapter
    document.addEventListener('DOMContentLoaded', () => {
        const firstReady = chaptersData.find(c => c.status === 'ready' && c.audio_path);
        if (firstReady) {
            selectChapter(firstReady.id, false);
        }

        // Start polling if book is processing
        if (bookStatus !== 'ready' && bookStatus !== 'failed') {
            startStatusPolling();
        }

        // Auto open book reader if #read hash is present
        if (window.location.hash === '#read') {
            setTimeout(openPdfModal, 300);
        }
    });

    function selectChapter(chapterId, autoPlay = true) {
        const chapter = chaptersData.find(c => c.id === chapterId);
        if (!chapter || chapter.status !== 'ready') return;

        currentChapterId = chapter.id;
        const trackTitleFormatted = `Pista #${chapter.chapter_number} - ${chapter.title}`;
        
        if (playerChapterTitle) playerChapterTitle.textContent = trackTitleFormatted;
        if (modalChapterTitle) modalChapterTitle.textContent = trackTitleFormatted;

        const downloadUrl = downloadBaseTemplate.replace('__ID__', chapter.id);
        if (btnPlayerDownload) btnPlayerDownload.href = downloadUrl;
        if (btnModalDownload) btnModalDownload.href = downloadUrl;

        // Update audio source
        audioEngine.src = streamBaseTemplate.replace('__ID__', chapter.id);
        audioEngine.playbackRate = parseFloat(playerPlaybackRate.value);

        // Highlight active chapter row
        document.querySelectorAll('.chapter-row').forEach(row => {
            row.classList.remove('bg-[#00ff87]/15', 'dark:bg-[#00ff87]/20', 'border-l-4', 'border-[#00ff87]');
        });
        const activeRow = document.getElementById(`chapterRow-${chapter.id}`);
        if (activeRow) {
            activeRow.classList.add('bg-[#00ff87]/15', 'dark:bg-[#00ff87]/20', 'border-l-4', 'border-[#00ff87]');
        }

        if (autoPlay) {
            audioEngine.play().then(() => {
                updatePlayIcons(true);
            }).catch(e => console.log('Autoplay prevented:', e));
        } else {
            updatePlayIcons(false);
        }
    }

    function handleChapterClick(chapterId) {
        const chapter = chaptersData.find(c => c.id === chapterId);
        if (!chapter || chapter.status !== 'ready') return;

        if (currentChapterId === chapter.id) {
            togglePlay();
        } else {
            selectChapter(chapter.id, true);
        }
    }

    function togglePlay() {
        if (!audioEngine.src) {
            const firstReady = chaptersData.find(c => c.status === 'ready');
            if (firstReady) selectChapter(firstReady.id, true);
            return;
        }

        if (audioEngine.paused) {
            audioEngine.play();
            updatePlayIcons(true);
        } else {
            audioEngine.pause();
            updatePlayIcons(false);
        }
    }

    function updatePlayIcons(isPlaying) {
        // Sticky bar icons
        if (iconMasterPlay && iconMasterPause) {
            iconMasterPlay.classList.toggle('hidden', isPlaying);
            iconMasterPause.classList.toggle('hidden', !isPlaying);
        }

        // Modal bar icons
        if (iconModalPlay && iconModalPause) {
            iconModalPlay.classList.toggle('hidden', isPlaying);
            iconModalPause.classList.toggle('hidden', !isPlaying);
        }

        // Update summary button icons
        const btnSummary = document.getElementById('btnPlaySummary');
        if (btnSummary) {
            const playIcon = btnSummary.querySelector('.icon-play');
            const pauseIcon = btnSummary.querySelector('.icon-pause');
            if (playIcon && pauseIcon) {
                if (currentChapterId === 'summary' && isPlaying) {
                    playIcon.classList.add('hidden');
                    pauseIcon.classList.remove('hidden');
                } else {
                    playIcon.classList.remove('hidden');
                    pauseIcon.classList.add('hidden');
                }
            }
        }

        // Update row icons
        chaptersData.forEach(ch => {
            const btn = document.getElementById(`btnPlayChapter-${ch.id}`);
            if (btn) {
                const playIcon = btn.querySelector('.icon-play');
                const pauseIcon = btn.querySelector('.icon-pause');
                if (playIcon && pauseIcon) {
                    if (ch.id === currentChapterId && isPlaying) {
                        playIcon.classList.add('hidden');
                        pauseIcon.classList.remove('hidden');
                    } else {
                        playIcon.classList.remove('hidden');
                        pauseIcon.classList.add('hidden');
                    }
                }
            }
        });
    }

    function playSummary() {
        const streamUrl = "{{ route('books.summary.stream', $book->id) }}";
        if (currentChapterId === 'summary') {
            togglePlay();
            return;
        }

        currentChapterId = 'summary';
        audioEngine.src = streamUrl;

        const titleText = "Resumen Ejecutivo — {{ addslashes($book->title) }}";
        if (playerChapterTitle) playerChapterTitle.textContent = titleText;
        if (modalChapterTitle) modalChapterTitle.textContent = titleText;

        // Deselect chapter rows
        document.querySelectorAll('.chapter-row').forEach(row => {
            row.classList.remove('bg-[#00ff87]/15', 'dark:bg-[#00ff87]/20', 'border-l-4', 'border-[#00ff87]');
        });

        audioEngine.play().then(() => {
            updatePlayIcons(true);
        }).catch(e => console.log('Autoplay prevented:', e));
    }

    // Audio Engine Event Listeners
    audioEngine.addEventListener('timeupdate', () => {
        if (!isNaN(audioEngine.duration) && audioEngine.duration > 0) {
            const pct = (audioEngine.currentTime / audioEngine.duration) * 100;
            const currentFormatted = formatTime(audioEngine.currentTime);
            const durationFormatted = formatTime(audioEngine.duration);

            if (playerScrubber) playerScrubber.value = pct;
            if (modalScrubber) modalScrubber.value = pct;
            if (playerCurrentTime) playerCurrentTime.textContent = currentFormatted;
            if (modalCurrentTime) modalCurrentTime.textContent = currentFormatted;
            if (playerDuration) playerDuration.textContent = durationFormatted;
            if (modalDuration) modalDuration.textContent = durationFormatted;
        }
    });

    audioEngine.addEventListener('loadedmetadata', () => {
        const durationFormatted = formatTime(audioEngine.duration);
        if (playerDuration) playerDuration.textContent = durationFormatted;
        if (modalDuration) modalDuration.textContent = durationFormatted;
    });

    // Auto next chapter upon finishing
    audioEngine.addEventListener('ended', () => {
        playNextChapter();
    });

    function playNextChapter() {
        if (!currentChapterId) return;
        const currentIndex = chaptersData.findIndex(c => c.id === currentChapterId);
        if (currentIndex >= 0 && currentIndex + 1 < chaptersData.length) {
            const nextCh = chaptersData[currentIndex + 1];
            if (nextCh.status === 'ready') {
                selectChapter(nextCh.id, true);
            }
        } else {
            updatePlayIcons(false);
        }
    }

    function playPrevChapter() {
        if (!currentChapterId) return;
        const currentIndex = chaptersData.findIndex(c => c.id === currentChapterId);
        if (currentIndex > 0) {
            const prevCh = chaptersData[currentIndex - 1];
            if (prevCh.status === 'ready') {
                selectChapter(prevCh.id, true);
            }
        }
    }

    // Scrubber seek synchronization
    if (playerScrubber) {
        playerScrubber.addEventListener('input', () => {
            if (!isNaN(audioEngine.duration)) {
                audioEngine.currentTime = (playerScrubber.value / 100) * audioEngine.duration;
                if (modalScrubber) modalScrubber.value = playerScrubber.value;
            }
        });
    }

    if (modalScrubber) {
        modalScrubber.addEventListener('input', () => {
            if (!isNaN(audioEngine.duration)) {
                audioEngine.currentTime = (modalScrubber.value / 100) * audioEngine.duration;
                if (playerScrubber) playerScrubber.value = modalScrubber.value;
            }
        });
    }

    // Skip controls synchronization
    function rewind15() {
        audioEngine.currentTime = Math.max(0, audioEngine.currentTime - 15);
    }
    function forward15() {
        audioEngine.currentTime = Math.min(audioEngine.duration || 0, audioEngine.currentTime + 15);
    }

    if (btnRewind15) btnRewind15.addEventListener('click', rewind15);
    if (btnModalRewind15) btnModalRewind15.addEventListener('click', rewind15);
    if (btnForward15) btnForward15.addEventListener('click', forward15);
    if (btnModalForward15) btnModalForward15.addEventListener('click', forward15);

    if (btnMasterPlay) btnMasterPlay.addEventListener('click', togglePlay);
    if (btnNextChapter) btnNextChapter.addEventListener('click', playNextChapter);
    if (btnModalNextChapter) btnModalNextChapter.addEventListener('click', playNextChapter);
    if (btnPrevChapter) btnPrevChapter.addEventListener('click', playPrevChapter);
    if (btnModalPrevChapter) btnModalPrevChapter.addEventListener('click', playPrevChapter);

    // Speed Rate synchronization across desktop, mobile and modal
    function syncPlaybackRate(rate) {
        audioEngine.playbackRate = parseFloat(rate);
        ['playerPlaybackRate', 'playerPlaybackRateMobile', 'modalPlaybackRate'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = rate;
        });
    }

    ['playerPlaybackRate', 'playerPlaybackRateMobile', 'modalPlaybackRate'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', (e) => syncPlaybackRate(e.target.value));
        }
    });

    // Mute toggle synchronization
    function toggleMute() {
        audioEngine.muted = !audioEngine.muted;
        const isMuted = audioEngine.muted;
        [iconVolumeHigh, iconModalVolumeHigh].forEach(el => {
            if (el) el.classList.toggle('hidden', isMuted);
        });
        [iconVolumeMuted, iconModalVolumeMuted].forEach(el => {
            if (el) el.classList.toggle('hidden', !isMuted);
        });
        const mobileVolHigh = document.querySelector('#btnMuteToggleMobile .icon-vol-high');
        const mobileVolMuted = document.querySelector('#btnMuteToggleMobile .icon-vol-muted');
        if (mobileVolHigh) mobileVolHigh.classList.toggle('hidden', isMuted);
        if (mobileVolMuted) mobileVolMuted.classList.toggle('hidden', !isMuted);
    }

    if (btnMuteToggle) btnMuteToggle.addEventListener('click', toggleMute);
    if (btnModalMuteToggle) btnModalMuteToggle.addEventListener('click', toggleMute);
    const btnMuteToggleMobile = document.getElementById('btnMuteToggleMobile');
    if (btnMuteToggleMobile) btnMuteToggleMobile.addEventListener('click', toggleMute);

    // Spacebar to play/pause
    window.addEventListener('keydown', (e) => {
        if (e.code === 'Space' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            togglePlay();
        }
    });

    // Polling function for background synthesis
    // ── Phase label map ──────────────────────────────────────────────
    const phaseLabels = {
        pending:      'En cola — esperando procesamiento…',
        extracting:   'Extrayendo texto del documento…',
        extracting_ocr: 'Extrayendo texto con OCR (Tesseract)…',
        synthesizing: (done, total) => total
            ? `Sintetizando pista ${done} de ${total} con voz AI…`
            : 'Sintetizando audio con voz AI…',
        ready:        '¡Listo para escuchar! 🎧',
        failed:       'Error en el procesamiento.',
    };

    function getPhaseLabel(status, processed, total, ocrUsed) {
        if (status === 'extracting' && ocrUsed) return phaseLabels.extracting_ocr;
        if (status === 'synthesizing') return phaseLabels.synthesizing(processed, total);
        return phaseLabels[status] || 'Procesando…';
    }

    function startStatusPolling() {
        let consecutiveErrors = 0;
        const interval = setInterval(() => {
            fetch(statusUrl)
                .then(res => res.json())
                .then(data => {
                    consecutiveErrors = 0;
                    bookStatus = data.status;

                    // Update progress bar
                    const progressBar  = document.getElementById('processingProgressBar');
                    const progressPct  = document.getElementById('processingPercentage');
                    const phaseLabel   = document.getElementById('processingDetail');
                    const chDone       = document.getElementById('procChDone');
                    const chTotal      = document.getElementById('procChTotal');
                    const ocrBadge     = document.getElementById('processingOcrBadge');

                    if (progressBar) progressBar.style.width = `${data.progress}%`;
                    if (progressPct) progressPct.textContent  = `${data.progress}%`;
                    if (chDone)      chDone.textContent        = data.processed_chapters ?? '0';
                    if (chTotal)     chTotal.textContent       = data.total_chapters     ?? '—';

                    // OCR badge — show if server reported ocr_used (future field) or status says so
                    const ocrActive = data.ocr_used || data.status === 'extracting_ocr';
                    if (ocrBadge) {
                        ocrBadge.classList.toggle('hidden',  !ocrActive);
                        ocrBadge.classList.toggle('inline-flex', ocrActive);
                    }

                    // Phase label
                    if (phaseLabel) {
                        phaseLabel.textContent = getPhaseLabel(
                            data.status,
                            data.processed_chapters,
                            data.total_chapters,
                            ocrActive
                        );
                    }

                    if (data.status === 'ready' || data.status === 'failed') {
                        clearInterval(interval);
                        location.reload();
                    }
                })
                .catch(err => {
                    consecutiveErrors++;
                    console.warn('Polling error:', err);
                    if (consecutiveErrors >= 10) clearInterval(interval); // stop after ~15 s of errors
                });
        }, 1500); // poll every 1.5 s
    }

    // Auto-play summary if ?play=summary in query parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('play') === 'summary') {
        setTimeout(() => {
            if (typeof playSummary === 'function') {
                playSummary();
            }
        }, 400);
    }
</script>
@endpush
