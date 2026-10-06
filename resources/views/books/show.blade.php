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

    <!-- Early Access Audio Download Recommendation Banner -->
    <div class="p-3 rounded-2xl bg-slate-100/80 dark:bg-[#071014]/80 border border-slate-200/80 dark:border-cyan-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs shadow-sm">
        <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold uppercase bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 flex-shrink-0">
                ⚠️ Beta Tip
            </span>
            <span class="text-slate-600 dark:text-sky-300">
                Recuerda pulsar el botón <strong class="text-slate-900 dark:text-cyan-200 font-mono font-bold">MP3</strong> en tus pistas para descargar y guardar tus audios permanentemente.
            </span>
        </div>
        <button type="button" onclick="openSupportModal()" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline self-end sm:self-auto flex-shrink-0">
            ¿Comentarios o sugerencias? &rarr;
        </button>
    </div>

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
    @include('books.partials.show-player-scripts')
@endpush
