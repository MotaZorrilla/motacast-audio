@extends('layouts.app')

@section('title', 'MotaCastAudio - Documentos y Libros a Audiolibros con IA')
@section('meta_description', 'Convierte PDFs, Word, texto e imágenes (OCR) en audiolibros con voces neuronales realistas, lectura sincronizada en vivo (Read-Along) y resúmenes ejecutivos.')
@section('og_title', 'MotaCastAudio - Documentos a Audiolibros con Inteligencia Artificial')
@section('og_description', 'Convierte al instante documentos PDF, Word, notas e imágenes con OCR en audiolibros con voces neuronales en español y reproductor interactivo.')
@section('og_url', route('home'))
@section('og_image', asset('images/motacast-og-banner.jpg'))

@section('content')
<div class="space-y-16 sm:space-y-24 -mt-2">

    <!-- Active Guest Book Continuity Banner (If session present) -->
    @if (session('guest_book_id'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 dark:bg-emerald-950/40 border border-emerald-500/30 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-md animate-pulse">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-[#00ff87] shadow-[0_0_10px_#00ff87]"></span>
                <span class="text-xs sm:text-sm font-bold text-slate-800 dark:text-cyan-200">
                    Tienes un audiolibro de prueba activo generado en esta sesión.
                </span>
            </div>
            <a href="{{ route('books.show', session('guest_book_id')) }}" class="btn-neon-tactile inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black shadow-sm text-slate-950 flex-shrink-0">
                <span>Continuar Escuchando mi Audiolibro</span>
                <svg class="w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    @endif

    <!-- =========================================================================
         1. HERO SECTION (Inspired by Kairos10 App Landing Architecture)
         ========================================================================= -->
    <section id="inicio" class="relative pt-4 sm:pt-10">
        <!-- Ambient Radial Glows -->
        <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-96 h-96 bg-[#00f0ff]/15 dark:bg-[#00ff87]/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- Hero Left: Value Proposition & CTAs -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                
                <!-- Badge Pill -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-mono font-bold bg-slate-100 dark:bg-cyan-950/60 text-cyan-700 dark:text-[#00f0ff] border border-slate-300 dark:border-cyan-800/60 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-[#00ff87] animate-ping"></span>
                    <span>MotaCastAudio v2.7 · IA & Síntesis Neuronal</span>
                </div>

                <!-- Main Punchy Title -->
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-slate-950 dark:text-cyan-100 leading-[1.12]">
                    Convierte Documentos en <br class="hidden sm:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-600 via-[#00c965] to-emerald-500 dark:from-[#00f0ff] dark:via-[#00ff87] dark:to-emerald-400 drop-shadow-[0_2px_15px_rgba(0,255,135,0.35)]">
                        Audiolibros Neuronales
                    </span>
                </h1>

                <!-- Persuasive Subtitle -->
                <p class="text-sm sm:text-base lg:text-lg text-slate-600 dark:text-sky-300/90 max-w-2xl mx-auto lg:mx-0 leading-relaxed font-medium">
                    Sube archivos <strong>PDF, Word, notas o imágenes con OCR</strong>. Escucha con voces humanas ultranaturales en español, 
                    <strong>lectura sincronizada en vivo (Read-Along)</strong> y resúmenes ejecutivos inmediatos para estudiar y trabajar en movimiento.
                </p>

                <!-- Action Buttons (Thumb-friendly & High-contrast) -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5 pt-2">
                    <a href="#probar" class="w-full sm:w-auto btn-neon-tactile px-6 py-3.5 rounded-2xl text-sm font-black text-slate-950 flex items-center justify-center gap-2.5 shadow-neon-md transition transform active:scale-95">
                        <svg class="w-5 h-5 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Probar Conversión Gratis</span>
                    </a>

                    <a href="#demo" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl text-sm font-bold text-slate-700 dark:text-cyan-300 bg-white/80 dark:bg-[#071014]/90 border border-slate-300 dark:border-cyan-800/80 hover:border-cyan-500 dark:hover:border-[#00ff87] shadow-sm hover:shadow-md transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-[#00c965] dark:text-[#00ff87]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Escuchar Muestra de Voz</span>
                    </a>
                </div>

                <!-- Trust Points -->
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-3 text-xs text-slate-500 dark:text-sky-400 font-medium">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#00c965] dark:text-[#00ff87]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        1 Ensayo Gratis sin registro
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#00c965] dark:text-[#00ff87]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        Voces neuronales en español
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#00c965] dark:text-[#00ff87]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        Lectura guiada sincronizada
                    </span>
                </div>

            </div>

            <!-- Hero Right: Bespoke 3D Mobile Device Mockup (Kairos Style) -->
            <div class="lg:col-span-5 flex justify-center relative">
                
                <!-- Mockup Glowing Halo -->
                <div class="absolute -inset-4 bg-gradient-to-r from-cyan-500/20 to-[#00ff87]/20 rounded-[44px] blur-2xl -z-10"></div>

                <!-- Smartphone Body Mockup -->
                <div class="w-full max-w-[320px] sm:max-w-[340px] rounded-[40px] p-3.5 bg-slate-900 border-4 border-slate-700/80 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.4)] relative">
                    
                    <!-- Dynamic Island / Speaker Notch -->
                    <div class="w-24 h-4 bg-slate-950 rounded-full mx-auto mb-3 flex items-center justify-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-slate-800"></span>
                        <span class="w-8 h-1 bg-slate-800 rounded-full"></span>
                    </div>

                    <!-- Screen Inset -->
                    <div class="rounded-[28px] bg-slate-950 p-4 space-y-4 border border-cyan-900/40 text-left overflow-hidden relative">
                        
                        <!-- Mini Header -->
                        <div class="flex items-center justify-between text-[11px] text-cyan-300 border-b border-cyan-950/60 pb-2">
                            <span class="font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#00ff87] animate-pulse"></span>
                                MotaCast Player
                            </span>
                            <span class="font-mono text-slate-400">02:37 / 18:40</span>
                        </div>

                        <!-- Book Cover Art Artifice -->
                        <div class="aspect-4/3 rounded-2xl bg-gradient-to-br from-cyan-950 via-[#071318] to-slate-950 border border-cyan-500/30 p-3.5 flex flex-col justify-between relative overflow-hidden group">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-[#00ff87]/20 text-[#00ff87] border border-[#00ff87]/30">PDF · OCR</span>
                                <span class="text-[10px] text-sky-400 font-mono">es-VE</span>
                            </div>
                            <div>
                                <p class="text-[10px] font-mono text-cyan-400 uppercase tracking-wider">Lectura Técnica</p>
                                <h3 class="text-xs font-black text-white leading-tight">Manual de Arquitectura Cloudflare</h3>
                            </div>
                        </div>

                        <!-- Animated Soundwave Equalizer -->
                        <div class="flex items-center justify-center gap-1 h-8 px-2 bg-slate-900/80 rounded-xl border border-cyan-950/40">
                            <span class="w-1 bg-[#00ff87] rounded-full h-3 animate-pulse"></span>
                            <span class="w-1 bg-cyan-400 rounded-full h-6 animate-pulse" style="animation-delay: 150ms"></span>
                            <span class="w-1 bg-[#00ff87] rounded-full h-4 animate-pulse" style="animation-delay: 300ms"></span>
                            <span class="w-1 bg-cyan-400 rounded-full h-7 animate-pulse" style="animation-delay: 75ms"></span>
                            <span class="w-1 bg-[#00ff87] rounded-full h-5 animate-pulse" style="animation-delay: 200ms"></span>
                            <span class="w-1 bg-cyan-400 rounded-full h-3 animate-pulse" style="animation-delay: 250ms"></span>
                        </div>

                        <!-- Scrubber Track -->
                        <div class="space-y-1">
                            <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-gradient-to-r from-cyan-400 to-[#00ff87] h-full rounded-full w-[42%]"></div>
                            </div>
                            <div class="flex justify-between text-[9px] font-mono text-slate-500">
                                <span>Capítulo 1: Configuración</span>
                                <span>18:40</span>
                            </div>
                        </div>

                        <!-- Tactile Controls Mockup -->
                        <div class="flex items-center justify-around pt-1">
                            <button type="button" class="p-2 text-slate-400 hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z" /></svg>
                            </button>
                            <button type="button" class="w-11 h-11 rounded-full bg-[#00ff87] text-slate-950 flex items-center justify-center font-black shadow-[0_0_15px_rgba(0,255,135,0.6)]">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            </button>
                            <button type="button" class="p-2 text-slate-400 hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.933 12.8a1 1 0 000-1.6L6.6 7.2A1 1 0 005 8v8a1 1 0 001.6.8l5.333-4zM19.933 12.8a1 1 0 000-1.6l-5.333-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.333-4z" /></svg>
                            </button>
                        </div>

                        <!-- Read-Along Live Karaoke Card Mockup -->
                        <div class="p-2.5 rounded-xl bg-slate-900 border border-[#00ff87]/30 text-[10px] space-y-1">
                            <span class="text-[9px] font-mono text-[#00ff87] font-bold uppercase flex items-center gap-1">
                                <span class="w-1 h-1 rounded-full bg-[#00ff87] animate-ping"></span>
                                Lectura Guiada Read-Along
                            </span>
                            <p class="text-slate-300 leading-snug">
                                <mark class="bg-[#00ff87]/20 text-[#00ff87] rounded px-1 font-bold">Cloudflare Tunnel permite exponer</mark> el servidor local sin abrir puertos físicos.
                            </p>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- =========================================================================
         2. QUICK TRIAL CONVERSION ENGINE (#probar)
         Preserves exact form fields & test requirements (assertSee: Agregar Documento o Libro)
         ========================================================================= -->
    <section id="probar" class="scroll-mt-20">
        <div class="card-tactile rounded-3xl p-6 sm:p-10 relative overflow-hidden space-y-6">
            
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-[#00ff87]/15 text-[#00c965] dark:text-[#00ff87] border border-[#00ff87]/30">
                    ⚡ Motor de Conversión Instantánea
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-950 dark:text-cyan-200 tracking-tight">
                    Agregar Documento o Libro
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-sky-300 font-medium">
                    Sube tu archivo o pega tu texto a continuación. Procesaremos tu documento y lo sintetizaremos en streaming de inmediato.
                </p>
            </div>

            <!-- Guest Trial Badge (Tested in GuestTrialWorkflowTest) -->
            @guest
                <div class="p-3.5 rounded-xl bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-400/40 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="text-cyan-900 dark:text-cyan-200">
                            <strong>Modo Prueba Gratuita:</strong> Tienes 1 conversión gratuita disponible sin registro previo.
                        </span>
                    </div>
                    <a href="{{ route('register') }}" class="font-bold text-cyan-700 dark:text-cyan-300 underline hover:text-cyan-500">Crear Cuenta</a>
                </div>
            @endguest

            <!-- Guía Visual en 3 Pasos (Tested in MasonicAndVoicePreviewTest) -->
            <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-teal-500/10 via-emerald-500/10 to-cyan-500/10 border border-teal-500/30 text-slate-800 dark:text-cyan-100 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-base">🎧</span>
                    <h3 class="text-xs sm:text-sm font-black uppercase tracking-wider text-teal-900 dark:text-[#00ff87]">
                        ¿Cómo usar MotaCastAudio? — Fácil en 3 Pasos
                    </h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="flex items-start gap-2.5 p-3 rounded-xl bg-white/70 dark:bg-[#071014]/70 border border-slate-200/80 dark:border-cyan-900/50 shadow-xs">
                        <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-950 font-black flex items-center justify-center text-xs shrink-0">1</span>
                        <div>
                            <strong class="text-slate-900 dark:text-cyan-200 block text-xs">Elige tu archivo o audio</strong>
                            <span class="text-[11px] text-slate-600 dark:text-sky-300/80 leading-snug block mt-0.5">Sube Word (.doc/.docx), PDF, fotos (OCR), audios (MP3, WAV) o pega texto directo.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5 p-3 rounded-xl bg-white/70 dark:bg-[#071014]/70 border border-slate-200/80 dark:border-cyan-900/50 shadow-xs">
                        <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-950 font-black flex items-center justify-center text-xs shrink-0">2</span>
                        <div>
                            <strong class="text-slate-900 dark:text-cyan-200 block text-xs">Configura según el formato</strong>
                            <span class="text-[11px] text-slate-600 dark:text-sky-300/80 leading-snug block mt-0.5">Si es texto, prueba y elige la voz deseada. Si es audio, activa la transcripción a texto (STT).</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5 p-3 rounded-xl bg-white/70 dark:bg-[#071014]/70 border border-slate-200/80 dark:border-cyan-900/50 shadow-xs">
                        <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-950 font-black flex items-center justify-center text-xs shrink-0">3</span>
                        <div>
                            <strong class="text-slate-900 dark:text-cyan-200 block text-xs">Escucha y exporta al instante</strong>
                            <span class="text-[11px] text-slate-600 dark:text-sky-300/80 leading-snug block mt-0.5">Tu audiolibro comenzará al instante. Podrás escucharlo o descargarlo en MP3, TXT o Markdown.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="uploadForm">
                @csrf

                <!-- Intake Mode Tabs: File Upload vs Direct Text Paste -->
                @include('books.partials.create-intake-tabs')

                <!-- Metadata Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider mb-1.5">
                            Título (Opcional)
                        </label>
                        <input 
                            type="text" 
                            name="title" 
                            id="title" 
                            value="{{ old('title') }}" 
                            placeholder="Se usará el nombre del documento si está vacío"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/50 rounded-xl text-slate-900 dark:text-cyan-200 placeholder-slate-400 dark:placeholder-sky-500/40 focus:outline-none focus:ring-2 focus:ring-[#00ff87] transition"
                        >
                    </div>
                    <div>
                        <label for="author" class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider mb-1.5">
                            Autor / Origen (Opcional)
                        </label>
                        <input 
                            type="text" 
                            name="author" 
                            id="author" 
                            value="{{ old('author') }}" 
                            placeholder="Ej. Héctor Mota Zorrilla"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/50 rounded-xl text-slate-900 dark:text-cyan-200 placeholder-slate-400 dark:placeholder-sky-500/40 focus:outline-none focus:ring-2 focus:ring-[#00ff87] transition"
                        >
                    </div>
                </div>

                <!-- Voice Selection & Submit Section -->
                @include('books.partials.create-voice-preferences')

                <!-- Audio / Media Strategy Section -->
                @include('books.partials.create-audio-preferences')

            </form>

        </div>
    </section>

    <!-- Modals & Scripts for Upload -->
    @include('books.partials.create-modals')
    @include('books.partials.create-scripts')

    <!-- =========================================================================
         3. CÓMO FUNCIONA (Inspired by Kairos10 3-Column Architecture)
         ========================================================================= -->
    <section id="como-funciona" class="scroll-mt-20 space-y-8">
        <div class="text-center max-w-2xl mx-auto space-y-2">
            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-cyan-500/10 text-cyan-600 dark:text-[#00f0ff] border border-cyan-500/30">
                Flujo de Trabajo
            </span>
            <h2 class="text-2xl sm:text-4xl font-black text-slate-950 dark:text-cyan-200 tracking-tight">
                Diseñado para Máxima Productividad
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-sky-300 font-medium">
                Tres etapas sencillas para pasar de documentos técnicos densos a conocimiento asimilable en audio.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Step 1 -->
            <div class="card-tactile rounded-3xl p-6 sm:p-7 space-y-4 hover:border-cyan-500/50 transition">
                <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-[#00f0ff] border border-cyan-500/30 flex items-center justify-center text-xl font-black">
                    01
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-cyan-100">Carga Universal</h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-sky-300/80 leading-relaxed">
                    Acepta archivos PDF, documentos Word (.docx), notas en Markdown, texto plano e imágenes de libros físicos con reconocimiento óptico de caracteres (OCR).
                </p>
            </div>

            <!-- Step 2 -->
            <div class="card-tactile rounded-3xl p-6 sm:p-7 space-y-4 hover:border-[#00ff87]/50 transition">
                <div class="w-12 h-12 rounded-2xl bg-[#00ff87]/10 text-[#00c965] dark:text-[#00ff87] border border-[#00ff87]/30 flex items-center justify-center text-xl font-black">
                    02
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-cyan-100">Segmentación & IA</h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-sky-300/80 leading-relaxed">
                    Normalización algorítmica de saltos de línea para lectura fluida, división por capítulos semánticos y generación de una sinopsis ejecutiva automática.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="card-tactile rounded-3xl p-6 sm:p-7 space-y-4 hover:border-emerald-500/50 transition">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-xl font-black">
                    03
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-cyan-100">Streaming & Read-Along</h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-sky-300/80 leading-relaxed">
                    Escucha de inmediato con soporte para rangos HTTP sin esperar toda la descarga, mientras el texto se resalta párrafo a párrafo en sincronía total.
                </p>
            </div>

        </div>
    </section>

    <!-- =========================================================================
         4. CARACTERÍSTICAS DE ÉLITE (Kairos 6-Card Feature Grid)
         ========================================================================= -->
    <section id="caracteristicas" class="scroll-mt-20 space-y-8">
        <div class="text-center max-w-2xl mx-auto space-y-2">
            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-[#00ff87]/15 text-[#00c965] dark:text-[#00ff87] border border-[#00ff87]/30">
                Capacidades Técnicas
            </span>
            <h2 class="text-2xl sm:text-4xl font-black text-slate-950 dark:text-cyan-200 tracking-tight">
                Tecnología Neuronal al Servicio de tu Estudio
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-sky-300 font-medium">
                Cada detalle fue concebido para que nunca pierdas el hilo de tus lecturas complejas.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            
            <!-- Feature 1 -->
            <div class="card-tactile rounded-3xl p-6 space-y-3">
                <span class="text-2xl">🎙️</span>
                <h3 class="text-base font-black text-slate-900 dark:text-cyan-200">Voces Humanas Realistas</h3>
                <p class="text-xs text-slate-600 dark:text-sky-300/80 leading-relaxed">
                    Modelos neuronales de alta fidelidad en español de Venezuela, Colombia, México y España, calibrados para entonación orgánica y pausas gramaticales exactas.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="card-tactile rounded-3xl p-6 space-y-3">
                <span class="text-2xl">📖</span>
                <h3 class="text-base font-black text-slate-900 dark:text-cyan-200">Karaoke Guiado Read-Along</h3>
                <p class="text-xs text-slate-600 dark:text-sky-300/80 leading-relaxed">
                    Resaltado en verde neón en tiempo real del párrafo que se está escuchando, con desplazamiento suave automático y toque para saltar (Click-to-Seek).
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="card-tactile rounded-3xl p-6 space-y-3">
                <span class="text-2xl">🔍</span>
                <h3 class="text-base font-black text-slate-900 dark:text-cyan-200">OCR para Documentos Físicos</h3>
                <p class="text-xs text-slate-600 dark:text-sky-300/80 leading-relaxed">
                    Pega fotos desde el portapapeles o sube capturas de páginas impresas. Nuestro pipeline extrae y limpia el texto para sintetizarlo al instante.
                </p>
            </div>

            <!-- Feature 4 -->
            <div class="card-tactile rounded-3xl p-6 space-y-3">
                <span class="text-2xl">⚡</span>
                <h3 class="text-base font-black text-slate-900 dark:text-cyan-200">Resúmenes Ejecutivos IA</h3>
                <p class="text-xs text-slate-600 dark:text-sky-300/80 leading-relaxed">
                    Generación automática de un briefing de 2 minutos con las tesis maestras de cada documento, reproducible con su propio reproductor independiente.
                </p>
            </div>

            <!-- Feature 5 -->
            <div class="card-tactile rounded-3xl p-6 space-y-3">
                <span class="text-2xl">📱</span>
                <h3 class="text-base font-black text-slate-900 dark:text-cyan-200">Ergonomía Mobile First</h3>
                <p class="text-xs text-slate-600 dark:text-sky-300/80 leading-relaxed">
                    Controles de audio táctiles de tamaño completo, visor de PDF embebido in-app y bajo consumo de batería para escuchar en trayectos diarios.
                </p>
            </div>

            <!-- Feature 6 -->
            <div class="card-tactile rounded-3xl p-6 space-y-3">
                <span class="text-2xl">🛡️</span>
                <h3 class="text-base font-black text-slate-900 dark:text-cyan-200">Privacidad y Streaming Ligero</h3>
                <p class="text-xs text-slate-600 dark:text-sky-300/80 leading-relaxed">
                    Aislamiento total de documentos por usuario, sin almacenar datos en servidores externos de terceros y con streaming de bajo ancho de banda.
                </p>
            </div>

        </div>
    </section>

    <!-- =========================================================================
         5. DEMOSTRACIÓN DE VOCES EN VIVO (#demo)
         ========================================================================= -->
    <section id="demo" class="scroll-mt-20">
        <div class="card-tactile rounded-3xl p-6 sm:p-10 border border-cyan-500/40 relative overflow-hidden">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-6 space-y-4 text-center lg:text-left">
                    <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-cyan-500/10 text-cyan-600 dark:text-[#00f0ff] border border-cyan-500/30">
                        Prueba en Vivo
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-950 dark:text-cyan-200 tracking-tight">
                        Escucha la Claridad de Nuestras Voces
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-sky-300 font-medium leading-relaxed">
                        Selecciona una voz y reproduce una muestra inmediata para comprobar la pronunciación natural y la ausencia de cortes robóticos.
                    </p>
                </div>

                <div class="lg:col-span-6 space-y-4">
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/60 space-y-3">
                        <label class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase">
                            Seleccionar Voz de Muestra
                        </label>
                        <select id="landingVoiceSelect" class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-white dark:bg-[#09151b] border border-slate-300 dark:border-cyan-800 rounded-xl text-slate-900 dark:text-cyan-200 outline-none">
                            <option value="es-VE-SebastianNeural">Sebastián (Venezuela - Masculina)</option>
                            <option value="es-VE-PaolaNeural">Paola (Venezuela - Femenina)</option>
                            <option value="es-ES-AlvaroNeural">Álvaro (España - Masculina)</option>
                            <option value="es-ES-ElviraNeural">Elvira (España - Femenina)</option>
                            <option value="es-MX-DaliaNeural">Dalia (México - Femenina)</option>
                            <option value="es-CO-GonzaloNeural">Gonzalo (Colombia - Masculina)</option>
                        </select>

                        <button 
                            type="button" 
                            id="landingBtnPlayPreview" 
                            class="w-full btn-neon-tactile py-3 px-4 rounded-xl text-xs font-black text-slate-950 flex items-center justify-center gap-2 shadow-md transition"
                        >
                            <svg class="w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            </svg>
                            <span id="landingPreviewBtnText">Escuchar Muestra de Audio</span>
                        </button>

                        <audio id="landingPreviewAudioPlayer" class="hidden"></audio>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- =========================================================================
         6. PLANES & CUOTAS (#precios)
         ========================================================================= -->
    <section id="precios" class="scroll-mt-20 space-y-8">
        <div class="text-center max-w-2xl mx-auto space-y-2">
            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-[#00ff87]/15 text-[#00c965] dark:text-[#00ff87] border border-[#00ff87]/30">
                Acceso & Límites
            </span>
            <h2 class="text-2xl sm:text-4xl font-black text-slate-950 dark:text-cyan-200 tracking-tight">
                Planes Transparentes para Todo Nivel
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-sky-300 font-medium">
                Comienza sin coste y escala según tus necesidades de volumen documental.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
            
            <!-- Plan 1 -->
            <div class="card-tactile rounded-3xl p-6 sm:p-7 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-sky-400">Invitado</span>
                    <h3 class="text-xl font-black text-slate-900 dark:text-cyan-200">Prueba Rápida</h3>
                    <div class="text-3xl font-black text-slate-900 dark:text-cyan-100">$0 <span class="text-xs font-normal text-slate-500">/ ensayo</span></div>
                    <ul class="text-xs text-slate-600 dark:text-sky-300/80 space-y-2.5">
                        <li class="flex items-center gap-2">✔ 1 Documento de prueba completo</li>
                        <li class="flex items-center gap-2">✔ Voces neuronales en español</li>
                        <li class="flex items-center gap-2">✔ Reproductor con streaming activo</li>
                        <li class="flex items-center gap-2 text-slate-400">✖ Sin guardado permanente de biblioteca</li>
                    </ul>
                </div>
                <a href="#probar" class="w-full py-2.5 rounded-xl text-xs font-bold text-center bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-cyan-200 hover:bg-slate-200 transition">
                    Probar Ahora
                </a>
            </div>

            <!-- Plan 2 (Highlighted) -->
            <div class="card-tactile rounded-3xl p-6 sm:p-7 flex flex-col justify-between space-y-6 border-2 border-[#00ff87] relative shadow-neon-md">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-[#00ff87] text-slate-950 shadow-sm">
                    Recomendado · Beta
                </span>
                <div class="space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#00c965] dark:text-[#00ff87]">Early Access</span>
                    <h3 class="text-xl font-black text-slate-900 dark:text-cyan-200">Usuario Registrado</h3>
                    <div class="text-3xl font-black text-slate-900 dark:text-cyan-100">$0 <span class="text-xs font-normal text-[#00c965] dark:text-[#00ff87] font-bold">Fase Beta Activa</span></div>
                    <ul class="text-xs text-slate-600 dark:text-sky-300/80 space-y-2.5">
                        <li class="flex items-center gap-2">✔ Hasta 3 Audiolibros simultáneos</li>
                        <li class="flex items-center gap-2">✔ Lectura Guiada Read-Along en tiempo real</li>
                        <li class="flex items-center gap-2">✔ Resúmenes ejecutivos con IA</li>
                        <li class="flex items-center gap-2">✔ Descarga de MP3 y transcripciones</li>
                        <li class="flex items-center gap-2">✔ Ampliación de cortesía con 1 clic</li>
                    </ul>
                </div>
                <a href="{{ route('register') }}" class="w-full btn-neon-tactile py-3 rounded-xl text-xs font-black text-center text-slate-950 shadow-md transition">
                    Crear Cuenta Gratuita
                </a>
            </div>

            <!-- Plan 3 -->
            <div class="card-tactile rounded-3xl p-6 sm:p-7 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-sky-400">Pro / Ilimitado</span>
                    <h3 class="text-xl font-black text-slate-900 dark:text-cyan-200">Investigación</h3>
                    <div class="text-3xl font-black text-slate-900 dark:text-cyan-100">Personalizado</div>
                    <ul class="text-xs text-slate-600 dark:text-sky-300/80 space-y-2.5">
                        <li class="flex items-center gap-2">✔ Cuota ilimitada de audiolibros</li>
                        <li class="flex items-center gap-2">✔ Prioridad de procesamiento en cola</li>
                        <li class="flex items-center gap-2">✔ Soporte preferencial con el Administrador</li>
                        <li class="flex items-center gap-2">✔ OCR masivo de expedientes y libros</li>
                    </ul>
                </div>
                <button type="button" onclick="openSupportModal()" class="w-full py-2.5 rounded-xl text-xs font-bold text-center bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-cyan-200 hover:bg-slate-200 transition">
                    Contactar Administrador
                </button>
            </div>

        </div>
    </section>

</div>

<script>
    // Live Voice Demo Player Handler
    document.addEventListener('DOMContentLoaded', () => {
        const btnPlay = document.getElementById('landingBtnPlayPreview');
        const selectVoice = document.getElementById('landingVoiceSelect');
        const audio = document.getElementById('landingPreviewAudioPlayer');
        const btnText = document.getElementById('landingPreviewBtnText');

        if (btnPlay && selectVoice && audio) {
            btnPlay.addEventListener('click', () => {
                if (!audio.paused) {
                    audio.pause();
                    btnText.textContent = 'Escuchar Muestra de Audio';
                    return;
                }

                const voice = selectVoice.value;
                const url = `{{ route('voices.preview') }}?voice=${encodeURIComponent(voice)}&t=${Date.now()}`;
                audio.src = url;
                btnText.textContent = 'Cargando voz...';

                audio.play()
                    .then(() => {
                        btnText.textContent = '⏸ Pausar Muestra';
                    })
                    .catch((err) => {
                        console.error('Error al reproducir muestra:', err);
                        btnText.textContent = 'Escuchar Muestra de Audio';
                    });

                audio.onended = () => {
                    btnText.textContent = 'Escuchar Muestra de Audio';
                };
            });
        }
    });
</script>
@endsection
