@extends('layouts.app')

@section('title', 'MotaCast Audio - Convierte tus Documentos en Podcasts Personales')

@section('full_width')
<div class="w-full space-y-16 sm:space-y-24 pb-16 overflow-x-hidden">

    <!-- HERO SECTION: Mobile Showcase inspired by Kairos -->
    <section class="relative pt-6 sm:pt-14 pb-12 sm:pb-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <!-- Radial Ambient Glow -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[350px] sm:w-[600px] h-[350px] sm:h-[600px] bg-gradient-to-tr from-indigo-600/20 via-violet-600/15 to-emerald-500/10 rounded-full blur-3xl pointer-events-none z-0"></div>

        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-8 items-center">
            
            <!-- Left Column: Copy & CTAs -->
            <div class="lg:col-span-7 text-center lg:text-left space-y-6">
                <!-- Early Access Pill -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 text-xs font-bold tracking-wide">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Modo Prueba Gratuita • Early Access v2.0</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-[1.15]">
                    Transforma tus Libros y Documentos en <span class="bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-400 bg-clip-text text-transparent">Podcasts Personales</span>.
                </h1>

                <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 font-medium max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    Convierte archivos PDF, Word, notas y textos extensos en capítulos de audio fluidos con voces neuronales hiper-realistas. Escucha mientras caminas, entrenas o estudias con nuestra experiencia móvil.
                </p>

                <!-- Action Buttons: Primary Tactile + Secondary Ghost -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5 pt-2">
                    <a href="{{ route('books.create') }}" class="btn-primary-tactile w-full sm:w-auto px-7 py-3.5 rounded-2xl font-black text-sm flex items-center justify-center gap-2.5 shadow-lg shadow-indigo-500/25 active:scale-95 transition">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Agregar Documento o Libro</span>
                    </a>

                    @auth
                        <a href="{{ route('books.index') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-white font-bold text-sm border border-slate-200 dark:border-slate-700 flex items-center justify-center gap-2 transition active:scale-95">
                            <span>Ir a mi Biblioteca</span>
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-800 dark:text-white font-bold text-sm border border-slate-200 dark:border-slate-700/80 flex items-center justify-center gap-2 transition active:scale-95">
                            <span>Iniciar Sesión</span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </a>
                    @endauth
                </div>

                <!-- Trust Micro-Badges -->
                <div class="grid grid-cols-3 gap-2 pt-4 border-t border-slate-200 dark:border-slate-800/80 max-w-lg mx-auto lg:mx-0 text-left">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center flex-shrink-0">
                            ✓
                        </div>
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">100% Web (Sin app externa)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center flex-shrink-0">
                            ✓
                        </div>
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Español Neutro & Acentos</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-violet-500/10 text-violet-500 flex items-center justify-center flex-shrink-0">
                            ✓
                        </div>
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Capítulos & Descarga MP3</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Interactive Phone Mockup (Adapted from Kairos10) -->
            <div class="lg:col-span-5 flex justify-center relative">
                <div class="relative w-full max-w-[320px] sm:max-w-[360px]">
                    <!-- Glowing Backing Halo -->
                    <div class="absolute -inset-4 bg-gradient-to-b from-indigo-500/20 via-violet-500/10 to-transparent rounded-[48px] blur-2xl"></div>

                    <!-- Smartphone Frame Container -->
                    <div class="relative rounded-[40px] p-2.5 bg-slate-900 border-4 border-slate-800 shadow-2xl ring-1 ring-white/10 overflow-hidden">
                        
                        <!-- Top Speaker / Dynamic Island Notch -->
                        <div class="absolute top-4 left-1/2 -translate-x-1/2 w-28 h-4 bg-slate-950 rounded-full z-20 flex items-center justify-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-800"></span>
                            <span class="w-2 h-2 rounded-full bg-indigo-500/60"></span>
                        </div>

                        <!-- Screen Content: Kairos Mockup App Preview -->
                        <div class="relative rounded-[32px] overflow-hidden bg-slate-950 aspect-[9/18] flex flex-col">
                            <img src="{{ asset('images/landing/hero-app-screens-800.png') }}" 
                                 alt="MotaCast Mobile Player" 
                                 class="w-full h-full object-cover object-top filter brightness-95 contrast-105"
                                 onerror="this.onerror=null; this.src='{{ asset('images/landing/app-screen-600.png') }}';">

                            <!-- Floating Glass Mini-Player Overlay -->
                            <div class="absolute bottom-4 left-3 right-3 p-3.5 rounded-2xl bg-slate-900/90 backdrop-blur-md border border-slate-700/60 shadow-xl flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white text-xs font-black flex-shrink-0 shadow-md">
                                        🎧
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-white truncate">Capítulo 1: Introducción</p>
                                        <p class="text-[10px] text-indigo-400 font-mono">Voz Álvaro • 1.15x</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                    <span class="text-[10px] font-bold text-emerald-400">EN VIVO</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- LIVE VOICE PREVIEW STRIP: Instant Gratification -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span>🎙️ Escucha la Claridad de Nuestras Voces</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Pruébalas en directo antes de subir tu propio libro. Sin sintetizadores robóticos.</p>
                </div>
                <span class="text-xs font-mono font-bold px-3 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    Microsoft Edge Neural Audio
                </span>
            </div>

            <!-- Voice Sampler Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                <!-- Voice 1: Alvaro -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">Álvaro (España)</p>
                        <p class="text-[10px] text-slate-500">Masculina • Narrativa fluida</p>
                    </div>
                    <button type="button" onclick="playVoiceSample('es-ES-AlvaroNeural', this)" class="w-9 h-9 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center shadow-sm transition active:scale-95" title="Reproducir muestra de Álvaro">
                        ▶
                    </button>
                </div>

                <!-- Voice 2: Elvira -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">Elvira (España)</p>
                        <p class="text-[10px] text-slate-500">Femenina • Clara y expresiva</p>
                    </div>
                    <button type="button" onclick="playVoiceSample('es-ES-ElviraNeural', this)" class="w-9 h-9 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center shadow-sm transition active:scale-95" title="Reproducir muestra de Elvira">
                        ▶
                    </button>
                </div>

                <!-- Voice 3: Jorge -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">Jorge (México)</p>
                        <p class="text-[10px] text-slate-500">Masculina • Cálida y neutra</p>
                    </div>
                    <button type="button" onclick="playVoiceSample('es-MX-JorgeNeural', this)" class="w-9 h-9 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center shadow-sm transition active:scale-95" title="Reproducir muestra de Jorge">
                        ▶
                    </button>
                </div>

                <!-- Voice 4: Sebastián -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">Sebastián (Venezuela)</p>
                        <p class="text-[10px] text-slate-500">Masculina • Convincente</p>
                    </div>
                    <button type="button" onclick="playVoiceSample('es-VE-SebastianNeural', this)" class="w-9 h-9 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center shadow-sm transition active:scale-95" title="Reproducir muestra de Sebastián">
                        ▶
                    </button>
                </div>
            </div>
            <audio id="landingVoiceAudio" class="hidden"></audio>
        </div>
    </section>

    <!-- HOW IT WORKS: 4-Step Process Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-500">Paso a Paso</span>
            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                ¿Cómo Funciona MotaCast Audio?
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Diseñado para simplificar al máximo la transformación de texto en audio consumible en cualquier dispositivo.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Step 1 -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3 relative">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-lg flex items-center justify-center">
                    01
                </div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Sube tu Archivo</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Soporta PDFs, documentos de Word (.docx), archivos de texto (.txt), Markdown (.md) o texto directo desde el portapapeles.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3 relative">
                <div class="w-12 h-12 rounded-2xl bg-violet-50 dark:bg-violet-950/60 text-violet-600 dark:text-violet-400 font-black text-lg flex items-center justify-center">
                    02
                </div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Segmentación Inteligente</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    El sistema detecta encabezados, títulos y capítulos automáticamente para dividir el libro en entregas independientes de 5 a 15 minutos.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3 relative">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-black text-lg flex items-center justify-center">
                    03
                </div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Síntesis Neuronal</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Se procesa en segundo plano con las voces neuronales más avanzadas. Puedes cerrar la pestaña y volver cuando esté listo.
                </p>
            </div>

            <!-- Step 4 -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3 relative">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 font-black text-lg flex items-center justify-center">
                    04
                </div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Escucha como un Podcast</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Disfruta de streaming con barra de progreso táctil, control de velocidad (0.85x a 1.35x) y descarga directa en MP3.
                </p>
            </div>
        </div>
    </section>

    <!-- FEATURES SHOWCASE GRID -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-500">Capacidades</span>
            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                Diseñado para Oyentes Exigentes
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Todo lo que necesitas para sustituir horas de lectura frente a la pantalla por audio de calidad profesional.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Feature 1 -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-500 flex items-center justify-center text-xl">
                    📱
                </div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Mobile-First Inmersivo</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Diseñado para la palma de tu mano. Barra inferior táctil, botones grandes para caminar y mini-player que te acompaña en toda la app.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-950/60 text-violet-500 flex items-center justify-center text-xl">
                    ⚡
                </div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Velocidades Dinámicas</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Ajusta la velocidad de narración a 1.0x, 1.15x, 1.25x o 1.35x sin distorsión tonal de la voz ni artefactos metálicos.
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-500 flex items-center justify-center text-xl">
                    📑
                </div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Lector Paralelo & Resumen</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Accede al texto completo extraído con formato limpio mientras escuchas, o consulta el resumen sintético generado para cada libro.
                </p>
            </div>

            <!-- Feature 4 -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-500 flex items-center justify-center text-xl">
                    🔍
                </div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">OCR de Imágenes & Capturas</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Pega una captura de pantalla o foto de una página de un libro físico y el motor OCR extraerá el texto de inmediato para narrarlo.
                </p>
            </div>

            <!-- Feature 5 -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-500 flex items-center justify-center text-xl">
                    💾
                </div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Descargas Offline en MP3</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Descarga capítulos individuales en MP3 de alta fidelidad para escuchar sin conexión en el avión o en zonas sin cobertura móvil.
                </p>
            </div>

            <!-- Feature 6 -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-500 flex items-center justify-center text-xl">
                    🔒
                </div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Privacidad y Seguridad</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Tus libros y audios están estrictamente aislados en tu cuenta. Nadie más tiene acceso a tus documentos ni a tus transcripciones.
                </p>
            </div>
        </div>
    </section>

    <!-- PRICING & EARLY ACCESS SECTION -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div class="text-center max-w-2xl mx-auto space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-500">Acceso & Planes</span>
            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                Planes Transparentes y Sin Fricciones
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Únete durante nuestra fase Early Access y disfruta de conversiones gratuitas para tus estudios y lecturas.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Plan 1: Early Access Gratuito -->
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border-2 border-indigo-500/40 shadow-xl space-y-6 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-500/10 text-indigo-500 border border-indigo-500/20">
                            Recomendado
                        </span>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white mt-2">Early Access</h3>
                        <p class="text-xs text-slate-500">Ideal para estudiantes y lectores cotidianos</p>
                    </div>
                    <div class="text-right">
                        <span class="text-3xl font-black text-slate-900 dark:text-white">$0</span>
                        <p class="text-[11px] text-slate-400">Gratis</p>
                    </div>
                </div>

                <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300">
                    <li class="flex items-center gap-2.5">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Hasta 3 audiolibros completos iniciales</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Todas las voces neuronales en español</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Reproductor móvil con mini-player</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Extensión de cortesía con 1 clic</span>
                    </li>
                </ul>

                <a href="{{ route('register') }}" class="btn-primary-tactile block text-center py-3 rounded-2xl font-black text-xs shadow-md transition active:scale-95">
                    Crear Cuenta Gratuita
                </a>
            </div>

            <!-- Plan 2: Académico / Pro -->
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-500/10 text-slate-500 border border-slate-500/20">
                            Institucional
                        </span>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white mt-2">Investigador Pro</h3>
                        <p class="text-xs text-slate-500">Para tesistas, investigadores y bibliotecas</p>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">A Medida</span>
                        <p class="text-[11px] text-slate-400">Sin costo durante Beta</p>
                    </div>
                </div>

                <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300">
                    <li class="flex items-center gap-2.5">
                        <span class="text-indigo-500 font-bold">✓</span>
                        <span>Cupo ampliado o ilimitado</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="text-indigo-500 font-bold">✓</span>
                        <span>Prioridad en cola de síntesis pesada</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="text-indigo-500 font-bold">✓</span>
                        <span>Soporte directo con el Administrador</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="text-indigo-500 font-bold">✓</span>
                        <span>Exportación en lotes</span>
                    </li>
                </ul>

                <a href="{{ route('login') }}" class="block text-center py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-white font-bold text-xs border border-slate-200 dark:border-slate-700 transition active:scale-95">
                    Consultar con el Administrador
                </a>
            </div>
        </div>
    </section>

    <!-- FINAL CALL TO ACTION (CTA) -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-8 sm:p-12 rounded-3xl bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-950 border border-indigo-800/40 text-center space-y-6 shadow-2xl relative overflow-hidden">
            <div class="relative z-10 space-y-4 max-w-xl mx-auto">
                <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                    ¿Listo para Escuchar tu Próximo Libro?
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 font-medium">
                    Sube tu primer documento y compruébalo tú mismo. Sin tarjetas ni registros obligatorios para probar.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('books.create') }}" class="btn-primary-tactile w-full sm:w-auto px-8 py-3.5 rounded-2xl font-black text-xs shadow-lg transition active:scale-95">
                        Agregar Documento o Libro
                    </a>
                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 transition active:scale-95">
                        Crear Cuenta Gratis
                    </a>
                </div>
            </div>
            <!-- Ambient Glow -->
            <div class="absolute -top-12 -right-12 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        </div>
    </section>

</div>

@push('scripts')
<script>
    let activeSampleButton = null;
    function playVoiceSample(voice, btn) {
        const audio = document.getElementById('landingVoiceAudio');
        if (!audio) return;

        if (activeSampleButton === btn && !audio.paused) {
            audio.pause();
            btn.innerHTML = '▶';
            btn.classList.remove('bg-emerald-600');
            btn.classList.add('bg-indigo-600');
            activeSampleButton = null;
            return;
        }

        if (activeSampleButton && activeSampleButton !== btn) {
            activeSampleButton.innerHTML = '▶';
            activeSampleButton.classList.remove('bg-emerald-600');
            activeSampleButton.classList.add('bg-indigo-600');
        }

        btn.innerHTML = '⏳';
        audio.src = `/voices/preview?voice=${encodeURIComponent(voice)}&speed=+0%25`;
        audio.play().then(() => {
            btn.innerHTML = '⏸';
            btn.classList.remove('bg-indigo-600');
            btn.classList.add('bg-emerald-600');
            activeSampleButton = btn;
        }).catch(err => {
            console.error('Audio sample playback error:', err);
            btn.innerHTML = '▶';
        });

        audio.onended = () => {
            btn.innerHTML = '▶';
            btn.classList.remove('bg-emerald-600');
            btn.classList.add('bg-indigo-600');
            activeSampleButton = null;
        };
    }
</script>
@endpush
@endsection
