@extends('layouts.app')

@php
    $cleanSummary = !empty($book->summary) ? trim(preg_replace('/\s+/', ' ', strip_tags($book->summary))) : '';
    $ogDescription = !empty($cleanSummary) 
        ? \Illuminate\Support\Str::limit($cleanSummary, 180, '...')
        : "Escucha '{$book->title}' de " . ($book->author ?: 'Documento personal') . " en formato podcast neuronal ({$book->chapters->count()} pistas, {$book->formatted_duration}). Generado con MotaCastAudio.";

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
<div class="space-y-5 pb-36 sm:pb-32">

    <!-- Top Navigation Breadcrumbs & Top Quick Actions -->
    <div class="flex items-center justify-between">
        @auth
            <a href="{{ route('books.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Volver a Mis Documentos</span>
            </a>
        @else
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-500/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Libro de Prueba</span>
                </span>
                <a href="{{ route('books.create') }}" class="btn-primary-tactile px-3 py-1 rounded-xl text-xs font-black inline-flex items-center gap-1.5 shadow-sm" title="Cargar otro documento">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition" title="Eliminar documento">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </form>
            @endauth
        </div>
    </div>

    <!-- Mobile-First Compact Podcast Header (<80px on Phones) -->
    <div class="block sm:hidden card-tactile rounded-2xl p-3.5">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white flex items-center justify-center flex-shrink-0 shadow-md text-lg">
                    🎧
                </div>
                <div class="min-w-0">
                    <h1 class="text-xs font-black text-slate-900 dark:text-white truncate leading-tight">{{ $book->title }}</h1>
                    <div class="flex items-center gap-1.5 text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                        <span class="truncate max-w-[110px]">{{ $book->author ?: 'Documento' }}</span>
                        <span>&bull;</span>
                        <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">{{ $book->formatted_duration }}</span>
                    </div>
                </div>
            </div>

            <!-- Mobile Quick Actions -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <button 
                    type="button" 
                    onclick="openPdfModal()"
                    class="btn-primary-tactile px-3.5 py-2 rounded-xl text-[11px] font-black flex items-center gap-1.5 shadow-sm active:scale-95 transition"
                    title="Leer Documento"
                >
                    <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Leer</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Desktop Header Card (Full Spanning on sm+ screens) -->
    <div class="hidden sm:block card-tactile rounded-3xl p-6 sm:p-7 relative overflow-hidden">
        <!-- Ambient decorative glow -->
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-center gap-6 relative z-10">
            <!-- Podcast Cover Artwork Avatar -->
            <div class="w-20 h-24 rounded-2xl bg-gradient-to-tr from-indigo-600 via-violet-600 to-indigo-900 text-white flex flex-col items-center justify-center shadow-lg flex-shrink-0 border border-white/20">
                <span class="text-3xl">🎧</span>
                <span class="text-[9px] font-black font-mono tracking-wider mt-1 uppercase text-indigo-200">PODCAST</span>
            </div>

            <!-- Title & Metadata -->
            <div class="flex-grow space-y-2 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span id="bookStatusBadge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $book->status === 'ready' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : ($book->hasFailed() ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800' : 'bg-amber-500/15 text-amber-500 border border-amber-500/30 animate-pulse') }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $book->status === 'ready' ? 'bg-emerald-500' : ($book->hasFailed() ? 'bg-rose-500' : 'bg-amber-500 animate-ping') }}"></span>
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

                <div class="flex items-center justify-between gap-4">
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight truncate" id="bookTitle">
                        {{ $book->title }}
                    </h1>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button 
                            type="button" 
                            onclick="openPdfModal()" 
                            class="btn-primary-tactile px-4 py-2.5 rounded-xl text-xs font-black flex items-center gap-2 shadow-md hover:scale-105 active:scale-95 transition"
                            title="Leer Documento o Transcripción en Pantalla"
                        >
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Leer Texto</span>
                        </button>
                        <a 
                            href="{{ route('books.transcription.download', ['book' => $book->id, 'format' => 'txt']) }}" 
                            download
                            class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition shadow-sm"
                            title="Descargar transcripción completa en formato .TXT"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Texto (.TXT)</span>
                        </a>
                    </div>
                </div>

                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate" id="bookAuthor">
                    {{ $book->author ?: 'Documento personal' }}
                </p>

                <!-- Stats Bar -->
                <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-slate-500 dark:text-slate-400 pt-1">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span id="bookChaptersCount">{{ $book->chapters->count() }} Capítulos</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span id="bookDuration" class="font-bold text-slate-900 dark:text-white font-mono">{{ $book->formatted_duration }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

    <!-- Recommendations Banner -->
    <div class="p-3.5 rounded-2xl bg-indigo-50/50 dark:bg-slate-900/60 border border-indigo-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs shadow-sm">
        <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold uppercase bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex-shrink-0">
                💡 Tip de Escucha
            </span>
            <span class="text-slate-600 dark:text-slate-300">
                Usa el botón <strong class="text-slate-900 dark:text-white font-mono font-bold">MP3</strong> en cualquier capítulo para descargarlo y escucharlo sin conexión en modo avión.
            </span>
        </div>
        <button type="button" onclick="openSupportModal()" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline self-end sm:self-auto flex-shrink-0">
            ¿Sugerencias para la Beta? &rarr;
        </button>
    </div>

    <!-- Chapter / Episode Playlist Section (Pocket Casts / Spotify style) -->
    <div class="card-tactile rounded-3xl overflow-hidden shadow-sm">
        <div class="p-4 sm:p-5 border-b border-slate-200/80 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-950/60 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Capítulos del Podcast</h2>
            </div>
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 font-mono" id="playlistCount">
                {{ $book->chapters->where('status', 'ready')->count() }} de {{ $book->chapters->count() }} listos
            </span>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800/60" id="chaptersList">
            @forelse ($book->chapters as $index => $chapter)
                <div 
                    class="chapter-row p-3.5 sm:p-4 flex items-center justify-between gap-3 hover:bg-indigo-50/40 dark:hover:bg-slate-800/40 transition cursor-pointer group {{ $index === 0 && $chapter->status === 'ready' ? 'bg-indigo-50/30 dark:bg-indigo-950/20' : '' }}"
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
                    <div class="flex items-center gap-3.5 min-w-0">
                        <!-- Play / Chapter Number Badge -->
                        <div class="chapter-play-btn w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center flex-shrink-0 transition shadow-sm font-bold" id="btnPlayChapter-{{ $chapter->id }}">
                            @if ($chapter->status === 'ready')
                                <svg class="w-4 h-4 ml-0.5 icon-play" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                </svg>
                                <svg class="w-4 h-4 icon-pause hidden" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            @elseif ($chapter->status === 'synthesizing')
                                <svg class="w-4 h-4 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            @else
                                <span class="text-xs font-mono font-bold">{{ $chapter->chapter_number }}</span>
                            @endif
                        </div>

                        <!-- Chapter Title and Word Count -->
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-mono font-bold text-slate-400 dark:text-slate-500">Cap. {{ $chapter->chapter_number }}</span>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white group-hover:text-indigo-500 dark:group-hover:text-indigo-400 transition truncate">
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
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-indigo-50 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-indigo-600 rounded-xl text-[11px] font-bold transition shadow-sm"
                                title="Descargar MP3"
                                onclick="event.stopPropagation();"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>MP3</span>
                            </a>
                        @elseif ($chapter->status === 'synthesizing')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                Generando...
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                En espera
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                    Aún no se han generado capítulos. El proceso está activo en segundo plano.
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- Sticky Floating Podcast Player Bar (Mobile Ergonomic Dock) -->
<div id="stickyPlayerBar" class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-slate-950/95 backdrop-blur-lg border-t border-slate-200 dark:border-slate-850 shadow-2xl py-2.5 px-3 sm:px-8 z-40 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-2 sm:gap-4">
        
        <!-- Track Info & Mobile Quick Controls -->
        <div class="flex items-center justify-between w-full md:w-1/4 gap-2.5 min-w-0">
            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate leading-tight" id="playerChapterTitle">Selecciona una pista</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate" id="playerBookTitle">{{ $book->title }}</p>
                </div>
            </div>

            <!-- Mobile-only inline speed & mute buttons -->
            <div class="flex md:hidden items-center gap-2 flex-shrink-0">
                <select id="playerPlaybackRateMobile" class="px-2 py-1 text-[11px] font-bold bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-xl border border-slate-300 dark:border-slate-800 focus:ring-1 focus:ring-indigo-500">
                    <option value="0.75">0.75x</option>
                    <option value="1.0" selected>1.0x</option>
                    <option value="1.25">1.25x</option>
                    <option value="1.5">1.5x</option>
                    <option value="2.0">2.0x</option>
                </select>
                <button type="button" id="btnMuteToggleMobile" class="p-1.5 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition" title="Silenciar">
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
        <div class="flex flex-col items-center gap-1.5 w-full md:w-2/4">
            <div class="flex items-center gap-3 sm:gap-5">
                <!-- Previous Chapter -->
                <button id="btnPrevChapter" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white transition" title="Pista Anterior">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Rewind 15s -->
                <button id="btnRewind15" class="p-2 text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition" title="Retroceder 15s">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z" />
                    </svg>
                </button>

                <!-- Master Play/Pause Tactile Button -->
                <button id="btnMasterPlay" class="w-11 h-11 sm:w-12 sm:h-12 rounded-full btn-primary-tactile flex items-center justify-center shadow-lg transition transform hover:scale-105 active:scale-95" title="Reproducir / Pausar">
                    <svg id="iconMasterPlay" class="w-5 h-5 sm:w-6 sm:h-6 ml-0.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                    </svg>
                    <svg id="iconMasterPause" class="w-5 h-5 sm:w-6 sm:h-6 hidden text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </button>

                <!-- Forward 15s -->
                <button id="btnForward15" class="p-2 text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition" title="Adelantar 15s">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.933 12.8a1 1 0 000-1.6L6.6 7.2A1 1 0 005 8v8a1 1 0 001.6.8l5.333-4zM19.933 12.8a1 1 0 000-1.6l-5.333-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.333-4z" />
                    </svg>
                </button>

                <!-- Next Chapter -->
                <button id="btnNextChapter" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white transition" title="Siguiente Pista">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Scrubber Timeline & Elapsed Time -->
            <div class="w-full flex items-center gap-2.5">
                <span id="playerCurrentTime" class="font-mono text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 w-9 text-right font-semibold">00:00</span>
                <input 
                    type="range" 
                    id="playerScrubber" 
                    min="0" 
                    max="100" 
                    value="0" 
                    class="w-full h-1.5 bg-slate-200 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-indigo-600 focus:outline-none"
                >
                <span id="playerDuration" class="font-mono text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 w-9 font-semibold">00:00</span>
            </div>
        </div>

        <!-- Desktop Right Side: Speed Selector, Volume, Download -->
        <div class="hidden md:flex items-center justify-end gap-3 w-full md:w-1/4">
            <!-- Speed Switcher -->
            <select id="playerPlaybackRate" class="px-2.5 py-1.5 text-xs font-bold bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-xl border border-slate-300 dark:border-slate-800 focus:ring-1 focus:ring-indigo-500 transition cursor-pointer">
                <option value="0.75">0.75x</option>
                <option value="1.0" selected>1.0x</option>
                <option value="1.25">1.25x</option>
                <option value="1.5">1.5x</option>
                <option value="2.0">2.0x</option>
            </select>

            <!-- Volume Mute Toggle -->
            <button id="btnMuteToggle" class="p-2 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition" title="Silenciar">
                <svg id="iconVolumeHigh" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                </svg>
                <svg id="iconVolumeMuted" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                </svg>
            </button>

            <!-- Download Current Track -->
            <a id="btnPlayerDownload" href="#" class="p-2 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition" title="Descargar MP3 de la pista actual">
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
    @include('books.partials.show-player-scripts')
@endpush
