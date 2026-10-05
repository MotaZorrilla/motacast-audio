@extends('layouts.app')

@section('title', 'Convertir Documento a Audiolibro - MotaCastAudio')
@section('meta_description', 'Convierte PDFs, Word (DOCX), texto directo e imágenes (OCR) en audiolibros interactivos con voces neuronales ultranaturales y resúmenes ejecutivos.')
@section('og_title', 'MotaCastAudio - Documentos a Audiolibros con IA')
@section('og_description', 'Sube tus libros, documentos o imágenes y escúchalos al instante con voces neuronales de alta fidelidad, navegación por capítulos y reproductor interactivo.')
@section('og_type', 'website')
@section('og_url', route('books.create'))
@section('og_image', asset('images/motacast-og-banner.jpg'))
@section('og_image_alt', 'MotaCastAudio - Conversión Inteligente a Audiolibros')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Back link -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ Auth::check() ? route('books.index') : route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-sky-400 hover:text-emerald-500 dark:hover:text-cyan-300 transition mb-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                <span>{{ Auth::check() ? 'Volver a Mis Documentos' : 'Inicio' }}</span>
            </a>
            <h1 class="text-2xl font-black text-slate-900 dark:text-cyan-200 tracking-tight">Agregar Documento o Libro</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-sky-300/80 mt-1">
                Convierte tus documentos Word, PDFs o textos en audiolibros con voces neuronales naturales de alta calidad.
            </p>
        </div>
    </div>

    <!-- Guía Visual para Usuarios (Onboarding Simple en 3 Pasos) -->
    <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-teal-500/10 via-emerald-500/10 to-cyan-500/10 border border-teal-500/30 text-slate-800 dark:text-cyan-100 shadow-sm">
        <div class="flex items-center gap-2 mb-3">
            <span class="text-base">🎧</span>
            <h2 class="text-xs sm:text-sm font-black uppercase tracking-wider text-teal-900 dark:text-[#00ff87]">
                ¿Cómo usar MotaCastAudio? — Fácil en 3 Pasos
            </h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-white/70 dark:bg-[#071014]/70 border border-slate-200/80 dark:border-cyan-900/50 shadow-xs">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-950 font-black flex items-center justify-center text-xs shrink-0">1</span>
                <div>
                    <strong class="text-slate-900 dark:text-cyan-200 block text-xs">Elige tu lectura</strong>
                    <span class="text-[11px] text-slate-600 dark:text-sky-300/80 leading-snug block mt-0.5">Sube Word (.doc/.docx), PDF o pega cualquier texto directo.</span>
                </div>
            </div>
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-white/70 dark:bg-[#071014]/70 border border-slate-200/80 dark:border-cyan-900/50 shadow-xs">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-950 font-black flex items-center justify-center text-xs shrink-0">2</span>
                <div>
                    <strong class="text-slate-900 dark:text-cyan-200 block text-xs">Prueba la voz</strong>
                    <span class="text-[11px] text-slate-600 dark:text-sky-300/80 leading-snug block mt-0.5">Pulsa <strong>«Escuchar muestra»</strong> para elegir la voz que prefieras.</span>
                </div>
            </div>
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-white/70 dark:bg-[#071014]/70 border border-slate-200/80 dark:border-cyan-900/50 shadow-xs">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-950 font-black flex items-center justify-center text-xs shrink-0">3</span>
                <div>
                    <strong class="text-slate-900 dark:text-cyan-200 block text-xs">Escucha de inmediato</strong>
                    <span class="text-[11px] text-slate-600 dark:text-sky-300/80 leading-snug block mt-0.5">Tu audiolibro comenzará al instante. Podrás escucharlo o descargarlo en MP3.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- User Quota or Guest Mode Notice -->
    @auth
        @if (!Auth::user()->isAdmin())
            <div class="p-3.5 rounded-xl bg-slate-100 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/50 text-xs flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#00ff87]"></span>
                    <span class="text-slate-700 dark:text-sky-300">
                        Cuota Beta: <strong>{{ Auth::user()->books()->count() }}</strong> de {{ Auth::user()->book_limit === -1 ? 'Ilimitados' : Auth::user()->book_limit }} libros utilizados.
                    </span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="font-bold text-[#00c965] dark:text-[#00ff87]">
                        {{ Auth::user()->remainingBooks() === -1 ? 'Sin límite' : Auth::user()->remainingBooks() . ' restantes' }}
                    </span>
                    <button type="button" onclick="openSupportModal()" class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hover:underline">
                        ¿Necesitas más?
                    </button>
                </div>
            </div>
        @endif
    @else
        <div class="p-3.5 rounded-xl bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-400/40 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                <span class="text-cyan-900 dark:text-cyan-200">
                    <strong>Modo Prueba Gratuita:</strong> Tienes 1 conversión gratuita disponible sin registro previo.
                </span>
            </div>
            <a href="{{ route('register') }}" class="font-bold text-cyan-700 dark:text-cyan-300 underline hover:text-cyan-500">Crear Cuenta</a>
        </div>
    @endauth

    <!-- Upload Form (Tactile 3D in Day Mode, Cyber Glow in Dark Mode) -->
    <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data" class="card-tactile rounded-2xl p-6 md:p-8 space-y-6" id="uploadForm">
        @csrf

        <!-- Intake Mode Tabs: File Upload vs Direct Text Paste -->
        <div>
            <div class="flex items-center p-1 rounded-2xl bg-slate-100 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/60 mb-3 shadow-inner">
                <button 
                    type="button" 
                    id="tabModeFile" 
                    onclick="switchInputMode('file')"
                    class="flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition bg-white dark:bg-[#0d1c22] text-slate-900 dark:text-[#00ff87] shadow-sm border border-slate-200 dark:border-cyan-800/60"
                >
                    <svg class="w-4 h-4 text-emerald-500 dark:text-[#00ff87]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span>Subir Documento</span>
                    <span class="hidden md:inline text-[10px] text-slate-400 font-normal">(PDF, DOCX, TXT, MD)</span>
                </button>
                <button 
                    type="button" 
                    id="tabModeText" 
                    onclick="switchInputMode('text')"
                    class="flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition text-slate-500 dark:text-sky-400 hover:text-slate-900 dark:hover:text-cyan-200"
                >
                    <svg class="w-4 h-4 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Pegar Texto Directo</span>
                    <span class="px-1.5 py-0.5 rounded-md text-[9px] font-mono font-black bg-[#00ff87]/20 text-[#00c965] dark:text-[#00ff87] border border-[#00ff87]/30">NUEVO</span>
                </button>
            </div>
            <input type="hidden" name="input_mode" id="inputModeHidden" value="file">
        </div>

        <!-- Section 1: File Drag & Drop Zone -->
        <div id="sectionFileDrop">
            <label class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider mb-2">
                Documento, Imagen o Audio a convertir <span class="text-rose-500">*</span>
            </label>
            <div 
                id="dropZone"
                class="border-2 border-dashed border-slate-300 dark:border-cyan-800/60 hover:border-[#00ff87] rounded-2xl p-8 text-center transition cursor-pointer bg-slate-50/70 dark:bg-[#071014]/60 hover:bg-[#00ff87]/5 group"
            >
                <input type="file" name="pdf_file" id="pdfFileInput" accept=".pdf,.docx,.doc,.txt,.md,.markdown,.png,.jpg,.jpeg,.webp,.bmp,.mp3,.wav,.m4a,.ogg" class="hidden">
                
                <div class="flex flex-col items-center justify-center space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-white dark:bg-[#0d1c22] border border-slate-300 dark:border-cyan-700/50 shadow-sm flex items-center justify-center text-emerald-600 dark:text-cyan-400 group-hover:scale-105 group-hover:shadow-neon-sm transition">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-slate-500 dark:text-sky-400/80 mt-1">Word (.docx, .doc), PDF, TXT, MD, Fotos (PNG, JPG) o Audio (MP3, WAV) — hasta 100 MB</p>
                        <div class="flex items-center justify-center gap-2 mt-2 flex-wrap">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800/40">WORD (.DOCX, .DOC)</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">PDF</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700/40">TXT/MD</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">📸 IMG (OCR)</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">🎙️ AUDIO (STT)</span>
                        </div>
                    </div>
                </div>

                <!-- Selected File Indicator (hidden by default) -->
                <div id="fileSelectedBox" style="display: none;" class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 bg-[#00ff87]/15 dark:bg-cyan-950/60 text-[#00c965] dark:text-cyan-300 border border-[#00ff87]/30 dark:border-cyan-500/40 rounded-xl text-xs font-semibold shadow-sm">
                    <svg class="w-4 h-4 text-[#00ff87] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                    </svg>
                    <span id="selectedFileName">archivo.pdf</span>
                    <span id="selectedFileSize" class="text-slate-500 dark:text-sky-400 font-mono"></span>
                </div>
            </div>
        </div>

        <!-- Section 2: Direct Text Area (Hidden by default) -->
        <div id="sectionRawText" class="hidden space-y-2">
            <div class="flex items-center justify-between">
                <label for="rawTextInput" class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">
                    Texto a convertir en voz <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center gap-2 flex-wrap">
                    <button 
                        type="button" 
                        onclick="document.getElementById('ocrImageInput').click()"
                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-300 border border-cyan-300 dark:border-cyan-800/60 hover:bg-cyan-500/20 transition flex items-center gap-1 shadow-sm"
                        title="Extraer texto de una imagen (también puedes pegar con Ctrl+V)"
                    >
                        <span>📸 OCR Imagen</span>
                    </button>
                    <input type="file" id="ocrImageInput" accept="image/*" class="hidden" onchange="handleOcrImageUpload(this)">

                    <button 
                        type="button" 
                        onclick="pasteFromClipboard()"
                        class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-emerald-50 dark:bg-cyan-950/60 text-[#00c965] dark:text-[#00ff87] border border-emerald-300 dark:border-cyan-800/60 hover:bg-[#00ff87]/20 transition flex items-center gap-1 shadow-sm"
                        title="Pegar automáticamente desde el portapapeles"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Pegar</span>
                    </button>
                    <button 
                        type="button" 
                        onclick="clearRawText()"
                        class="px-2 py-1 text-[11px] font-medium text-slate-400 hover:text-rose-500 transition"
                    >
                        Limpiar
                    </button>
                </div>
            </div>

            <!-- OCR Extraction Status Indicator (Hidden by default) -->
            <div id="ocrStatusBox" class="hidden p-2.5 rounded-xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-800 dark:text-cyan-300 text-xs font-semibold items-center gap-2">
                <svg class="w-4 h-4 animate-spin text-cyan-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span id="ocrStatusText">Extrayendo texto de la imagen con OCR...</span>
            </div>

            <textarea 
                name="raw_text" 
                id="rawTextInput" 
                rows="9"
                placeholder="Pega aquí el artículo, ensayo, notas o presiona Ctrl+V para pegar capturas de pantalla/imágenes con OCR automático...&#10;&#10;Consejo: Puedes separar secciones con doble salto de línea o encabezados (# Título) para crear capítulos separados automáticamente."
                class="w-full px-4 py-3 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/50 rounded-2xl text-slate-900 dark:text-cyan-200 placeholder-slate-400 dark:placeholder-sky-500/40 focus:outline-none focus:ring-2 focus:ring-[#00ff87] font-mono leading-relaxed transition resize-y"
            >{{ old('raw_text') }}</textarea>

            <!-- Text Metrics & Dynamic Listening Estimation -->
            <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-sky-400 font-mono px-1">
                <div class="flex items-center gap-3 flex-wrap">
                    <span><strong id="counterWords" class="text-slate-800 dark:text-cyan-200 font-bold">0</strong> palabras</span>
                    <span><strong id="counterChars" class="text-slate-800 dark:text-cyan-200 font-bold">0</strong> / 50,000 car.</span>
                    <span id="counterLimitWarning" class="hidden text-rose-500 font-bold text-[10px] animate-pulse">⚠️ Límite de 50k superado</span>
                </div>
                <div class="flex items-center gap-1.5 text-emerald-600 dark:text-[#00ff87] font-bold">
                    <span>⏱️ Estimado:</span>
                    <span id="counterEstTime">~0 min de audio</span>
                </div>
            </div>
        </div>


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

        <!-- Admin Only: Assign Book to specific user -->
        @auth
            @if (Auth::user()->isAdmin() && isset($registeredUsers) && $registeredUsers->count() > 0)
                <div class="p-3.5 rounded-xl bg-teal-50/50 dark:bg-[#071014] border border-teal-500/30">
                    <label for="assigned_user_id" class="block text-xs font-bold text-teal-800 dark:text-cyan-300 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Asignar este libro a un usuario específico (Privilegio Admin):</span>
                    </label>
                    <select 
                        name="assigned_user_id" 
                        id="assigned_user_id"
                        class="w-full px-3 py-2 text-xs bg-white dark:bg-[#0d1c22] border border-teal-300 dark:border-cyan-800/60 rounded-xl text-slate-900 dark:text-cyan-200 focus:ring-2 focus:ring-[#00ff87] outline-none font-medium"
                    >
                        <option value="{{ Auth::id() }}">Mí mismo ({{ Auth::user()->name }})</option>
                        @foreach ($registeredUsers as $u)
                            @if ($u->id !== Auth::id())
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            @endif
        @endauth

        <!-- Voice & Reading Preferences -->
        <div class="border-t border-slate-200 dark:border-cyan-950/60 pt-5 space-y-4">
            <h3 class="text-xs font-bold text-slate-900 dark:text-cyan-200 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-[#00ff87]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                </svg>
                <span>Configuración de Voz y Lectura</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Voice Selector -->
                <div class="md:col-span-1">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="voice" class="block text-xs font-semibold text-slate-700 dark:text-sky-300">
                            Voz Narradora
                        </label>
                        <button 
                            type="button" 
                            id="btnVoicePreview"
                            onclick="toggleVoicePreview()"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-bold text-emerald-600 dark:text-[#00ff87] bg-emerald-50 dark:bg-[#00ff87]/10 hover:bg-emerald-100 dark:hover:bg-[#00ff87]/20 border border-emerald-300 dark:border-[#00ff87]/30 transition"
                            title="Escuchar una muestra de esta voz"
                        >
                            <span id="previewVoiceIcon">🔊</span>
                            <span id="previewVoiceText">Escuchar muestra</span>
                        </button>
                    </div>
                    <select 
                        name="voice" 
                        id="voice" 
                        onchange="onVoiceChanged()"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/50 rounded-xl text-slate-900 dark:text-cyan-200 focus:outline-none focus:ring-2 focus:ring-[#00ff87] transition"
                    >
                        @foreach ($voices as $v)
                            <option value="{{ $v['id'] }}" {{ $v['recommended'] ? 'selected' : '' }}>
                                {{ $v['name'] }}
                            </option>
                        @endforeach
                    </select>
                    <!-- Floating Voice Preview Status Badge -->
                    <div id="voicePreviewPlayerContainer" class="hidden mt-1.5 flex items-center gap-2 p-1.5 rounded-lg bg-emerald-500/15 border border-emerald-500/30 text-[10px] text-emerald-700 dark:text-[#00ff87]">
                        <span class="inline-block w-2 h-2 rounded-full bg-[#00ff87] animate-ping shrink-0"></span>
                        <span id="voicePreviewStatus" class="font-medium truncate">Reproduciendo muestra de voz...</span>
                    </div>
                </div>

                <!-- Speed / Rate -->
                <div>
                    <label for="speed_rate" class="block text-xs font-semibold text-slate-700 dark:text-sky-300 mb-1.5">
                        Velocidad
                    </label>
                    <select 
                        name="speed_rate" 
                        id="speed_rate" 
                        onchange="onVariantChanged()"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/50 rounded-xl text-slate-900 dark:text-cyan-200 focus:outline-none focus:ring-2 focus:ring-[#00ff87] transition"
                    >
                        <option value="-20%">Lenta (-20%)</option>
                        <option value="-10%">Pausada (-10%)</option>
                        <option value="+0%" selected>Natural (Recomendada)</option>
                        <option value="+10%">Ágil (+10%)</option>
                        <option value="+20%">Rápida (+20%)</option>
                        <option value="+30%">Ultra Rápida (+30%)</option>
                    </select>
                </div>

                <!-- Pitch -->
                <div>
                    <label for="pitch" class="block text-xs font-semibold text-slate-700 dark:text-sky-300 mb-1.5">
                        Tono
                    </label>
                    <select 
                        name="pitch" 
                        id="pitch" 
                        onchange="onVariantChanged()"
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/50 rounded-xl text-slate-900 dark:text-cyan-200 focus:outline-none focus:ring-2 focus:ring-[#00ff87] transition"
                    >
                        <option value="-20Hz">Grave (-20Hz)</option>
                        <option value="-10Hz">Profundo (-10Hz)</option>
                        <option value="+0Hz" selected>Estándar (+0Hz)</option>
                        <option value="+10Hz">Claro (+10Hz)</option>
                        <option value="+20Hz">Agudo (+20Hz)</option>
                    </select>
                </div>
            </div>

            <!-- Intelligent Masonic & Normalization Badge (Hidden by default; appears only when detected) -->
            <div id="masonicNoticeBox" class="hidden p-3 rounded-xl bg-slate-100 dark:bg-[#071014] border border-slate-200 dark:border-cyan-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs transition duration-300">
                <div class="flex items-center gap-2">
                    <span class="text-sm shrink-0">🏛️</span>
                    <div class="text-slate-700 dark:text-sky-300">
                        <strong>Modo Simbólico & Masónico Detectado:</strong> Expansión fonética activa de fórmulas litúrgicas y abreviaturas ritualísticas (Q∴H∴ ➔ Querido Hermano, V∴M∴, GADU, etc.).
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-600 dark:text-[#00ff87] border border-emerald-500/30 shrink-0 self-start sm:self-auto">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00ff87] animate-pulse"></span>
                    Auto-Activado
                </span>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button 
                type="submit" 
                id="submitBtn"
                class="w-full py-3.5 px-6 btn-neon-tactile rounded-xl text-sm font-black transition flex items-center justify-center gap-2"
            >
                <svg class="w-5 h-5 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                </svg>
                <span id="submitBtnText">Comenzar Extracción y Síntesis</span>
            </button>
        </div>

    </form>

    <!-- Processing Loading Overlay (Instant feedback on submission) -->
    <div id="submitOverlay" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex flex-col items-center justify-center p-4">
        <div class="card-tactile rounded-3xl p-6 sm:p-8 max-w-sm w-full text-center space-y-4 border border-[#00ff87]/30 shadow-neon-lg">
            <div class="relative w-16 h-16 mx-auto">
                <span class="absolute inset-0 rounded-full border-4 border-[#00ff87]/20 animate-pulse"></span>
                <svg class="w-16 h-16 animate-spin text-[#00ff87]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-cyan-200">Subiendo Documento</h3>
                <p class="text-xs text-slate-500 dark:text-sky-300 mt-1" id="overlayStatusMsg">Iniciando pipeline neuronal de extracción y voz...</p>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                <div class="bg-[#00ff87] h-2 w-full animate-pulse"></div>
            </div>
        </div>
    </div>

    <!-- Text Limit Warning & Guidance Modal (Direct Paste Limiter) -->
    <div id="textLimitModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="card-tactile rounded-3xl p-6 sm:p-7 max-w-lg w-full space-y-5 border border-amber-500/40 shadow-2xl relative">
            <!-- Modal Header -->
            <div class="flex items-start gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center flex-shrink-0 text-amber-500 shadow-sm">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="space-y-1 min-w-0 flex-1">
                    <h3 class="text-base font-black text-slate-900 dark:text-cyan-200">Límite de Pegado Directo Excedido</h3>
                    <p class="text-xs text-slate-500 dark:text-sky-300">
                        El texto detectado sobrepasa la capacidad recomendada para pegado directo.
                    </p>
                </div>
                <button type="button" onclick="closeTextLimitModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg transition" title="Cerrar ventana">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Stats Badge Comparison -->
            <div class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-slate-100 dark:bg-[#071014] border border-slate-200 dark:border-cyan-950/60 font-mono text-center">
                <div class="space-y-0.5">
                    <span class="text-[10px] uppercase text-rose-500 font-bold block">Texto Detectado</span>
                    <span id="modalDetectedChars" class="text-sm font-black text-rose-600 dark:text-rose-400">0 car.</span>
                    <span id="modalDetectedWords" class="text-[10px] text-slate-500 dark:text-sky-400 block">(0 palabras)</span>
                </div>
                <div class="space-y-0.5 border-l border-slate-200 dark:border-cyan-900/40">
                    <span class="text-[10px] uppercase text-emerald-600 dark:text-[#00ff87] font-bold block">Límite Permitido</span>
                    <span class="text-sm font-black text-emerald-600 dark:text-[#00ff87]">50,000 car.</span>
                    <span class="text-[10px] text-slate-500 dark:text-sky-400 block">(~10,000 pal. / ~1h audio)</span>
                </div>
            </div>

            <!-- Explanatory recommendation -->
            <div class="space-y-2 text-xs text-slate-600 dark:text-sky-200 leading-relaxed bg-emerald-500/5 border border-emerald-500/20 p-3.5 rounded-2xl">
                <p class="font-bold text-slate-900 dark:text-cyan-100 flex items-center gap-1.5">
                    <span>💡 Recomendación para documentos extensos:</span>
                </p>
                <p>
                    El pegado directo está diseñado para artículos breves, notas o capítulos individuales. Si deseas convertir un libro completo o tesis, te recomendamos <strong>subirlo como archivo</strong> (<span class="font-mono text-emerald-600 dark:text-[#00ff87]">.pdf, .docx, .txt</span>).
                </p>
                <p class="text-[11px] text-slate-500 dark:text-sky-400">
                    Al subirlo como archivo, el sistema dividirá automáticamente todo el documento en pistas organizadas por capítulos sin riesgo de saturación ni truncado.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2 pt-1">
                <button 
                    type="button" 
                    onclick="switchToUploadFromModal()"
                    class="btn-neon-tactile px-4 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-2 shadow-sm"
                >
                    <svg class="w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Cambiar a Subir Archivo</span>
                </button>
                <button 
                    type="button" 
                    onclick="truncateAndApplyText()"
                    class="px-3.5 py-2.5 text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 rounded-xl transition flex items-center justify-center gap-1.5"
                >
                    <span>✂️ Recortar a 50,000 car.</span>
                </button>
                <button 
                    type="button" 
                    onclick="closeTextLimitModal()"
                    class="px-3 py-2 text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition text-center"
                >
                    Cancelar
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    let currentInputMode = 'file';
    const tabModeFile = document.getElementById('tabModeFile');
    const tabModeText = document.getElementById('tabModeText');
    const sectionFileDrop = document.getElementById('sectionFileDrop');
    const sectionRawText = document.getElementById('sectionRawText');
    const inputModeHidden = document.getElementById('inputModeHidden');
    const rawTextInput = document.getElementById('rawTextInput');
    const counterWords = document.getElementById('counterWords');
    const counterChars = document.getElementById('counterChars');
    const counterEstTime = document.getElementById('counterEstTime');

    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('pdfFileInput');
    const fileLabel = document.getElementById('fileLabel');
    const fileSelectedBox = document.getElementById('fileSelectedBox');
    const selectedFileName = document.getElementById('selectedFileName');
    const selectedFileSize = document.getElementById('selectedFileSize');
    const uploadForm = document.getElementById('uploadForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitBtnText = document.getElementById('submitBtnText');
    const submitOverlay = document.getElementById('submitOverlay');

    // Text limit configurations & modal references
    const MAX_TEXT_CHARS = 50000;
    let pendingExcessText = '';
    const textLimitModal = document.getElementById('textLimitModal');
    const modalDetectedChars = document.getElementById('modalDetectedChars');
    const modalDetectedWords = document.getElementById('modalDetectedWords');
    const counterLimitWarning = document.getElementById('counterLimitWarning');

    function showTextLimitModal(incomingLen, totalLen, fullTextCandidate) {
        pendingExcessText = fullTextCandidate || '';
        const detected = incomingLen || totalLen || 0;
        if (modalDetectedChars) modalDetectedChars.textContent = `${detected.toLocaleString()} car.`;
        if (modalDetectedWords) {
            const words = (pendingExcessText.match(/\S+/g) || []).length;
            modalDetectedWords.textContent = `(${words.toLocaleString()} palabras)`;
        }
        if (textLimitModal) {
            textLimitModal.classList.remove('hidden');
            textLimitModal.classList.add('flex');
        }
    }

    function closeTextLimitModal() {
        if (textLimitModal) {
            textLimitModal.classList.add('hidden');
            textLimitModal.classList.remove('flex');
        }
        pendingExcessText = '';
    }

    function switchToUploadFromModal() {
        closeTextLimitModal();
        switchInputMode('file');
        if (dropZone) dropZone.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function truncateAndApplyText() {
        if (!rawTextInput) return;
        const source = pendingExcessText || rawTextInput.value;
        rawTextInput.value = source.slice(0, MAX_TEXT_CHARS);
        closeTextLimitModal();
        updateTextCounters();
        rawTextInput.focus();
    }

    function switchInputMode(mode) {
        currentInputMode = mode;
        if (inputModeHidden) inputModeHidden.value = mode;

        if (mode === 'file') {
            sectionFileDrop.classList.remove('hidden');
            sectionRawText.classList.add('hidden');

            tabModeFile.className = "flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition bg-white dark:bg-[#0d1c22] text-slate-900 dark:text-[#00ff87] shadow-sm border border-slate-200 dark:border-cyan-800/60";
            tabModeText.className = "flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition text-slate-500 dark:text-sky-400 hover:text-slate-900 dark:hover:text-cyan-200";
        } else {
            sectionFileDrop.classList.add('hidden');
            sectionRawText.classList.remove('hidden');

            tabModeText.className = "flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition bg-white dark:bg-[#0d1c22] text-slate-900 dark:text-[#00ff87] shadow-sm border border-slate-200 dark:border-cyan-800/60";
            tabModeFile.className = "flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition text-slate-500 dark:text-sky-400 hover:text-slate-900 dark:hover:text-cyan-200";

            if (rawTextInput) {
                rawTextInput.focus();
                updateTextCounters();
            }
        }
    }

    function updateTextCounters() {
        if (!rawTextInput) return;
        const text = rawTextInput.value;
        const chars = text.length;
        const words = (text.match(/\S+/g) || []).length;
        const minutes = Math.max(1, Math.ceil(words / 150)); // ~150 words per minute speaking rate

        if (counterWords) counterWords.textContent = words.toLocaleString();
        if (counterChars) {
            counterChars.textContent = chars.toLocaleString();
            if (chars > MAX_TEXT_CHARS) {
                counterChars.className = "text-rose-600 dark:text-rose-400 font-black";
                if (counterLimitWarning) counterLimitWarning.classList.remove('hidden');
            } else if (chars > MAX_TEXT_CHARS * 0.9) {
                counterChars.className = "text-amber-500 font-bold";
                if (counterLimitWarning) counterLimitWarning.classList.add('hidden');
            } else {
                counterChars.className = "text-slate-800 dark:text-cyan-200 font-bold";
                if (counterLimitWarning) counterLimitWarning.classList.add('hidden');
            }
        }
        if (counterEstTime) {
            counterEstTime.textContent = words > 0 ? `~${minutes} min de audio` : '~0 min de audio';
        }
    }

    if (rawTextInput) {
        rawTextInput.addEventListener('input', updateTextCounters);
        rawTextInput.addEventListener('paste', (e) => {
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            if (!pasted) return;
            const currentLen = rawTextInput.value.length;
            const totalLen = currentLen + pasted.length;
            if (totalLen > MAX_TEXT_CHARS) {
                e.preventDefault();
                const fullCandidate = rawTextInput.value + pasted;
                showTextLimitModal(pasted.length, totalLen, fullCandidate);
            }
        });
        updateTextCounters();
    }

    async function pasteFromClipboard() {
        try {
            if (!navigator.clipboard) {
                alert('Por favor presiona Ctrl+V para pegar directamente.');
                return;
            }
            const text = await navigator.clipboard.readText();
            if (text && text.trim()) {
                const currentLen = rawTextInput ? rawTextInput.value.length : 0;
                const totalLen = currentLen + text.length;
                if (totalLen > MAX_TEXT_CHARS) {
                    const fullCandidate = (rawTextInput ? rawTextInput.value : '') + text;
                    showTextLimitModal(text.length, totalLen, fullCandidate);
                    return;
                }
                rawTextInput.value = (rawTextInput.value ? rawTextInput.value + "\n\n" : '') + text;
                updateTextCounters();
            } else {
                alert('El portapapeles de texto está vacío. Si copiaste una imagen, presiona Ctrl+V en esta pantalla para hacerle OCR.');
            }
        } catch (err) {
            console.warn('Clipboard read error:', err);
            alert('Por favor pega manualmente el texto usando Ctrl+V.');
        }
    }

    function clearRawText() {
        if (rawTextInput) {
            rawTextInput.value = '';
            updateTextCounters();
            rawTextInput.focus();
        }
    }

    // OCR Image Upload & Paste Handlers
    function handleOcrImageUpload(input) {
        if (input.files && input.files.length > 0) {
            uploadAndOcrImage(input.files[0]);
            input.value = '';
        }
    }

    function uploadAndOcrImage(file) {
        const box = document.getElementById('ocrStatusBox');
        const textLabel = document.getElementById('ocrStatusText');
        if (box) box.classList.remove('hidden');
        if (box) box.classList.add('flex');
        if (textLabel) textLabel.textContent = `Procesando OCR en "${file.name || 'imagen'}"...`;

        const formData = new FormData();
        formData.append('image', file);

        fetch("{{ route('books.ocr.preview') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (box) {
                box.classList.add('hidden');
                box.classList.remove('flex');
            }
            if (data.success && data.text) {
                switchInputMode('text');
                const prev = rawTextInput.value.trim();
                const combined = prev ? (prev + "\n\n" + data.text) : data.text;
                if (combined.length > MAX_TEXT_CHARS) {
                    showTextLimitModal(data.text.length, combined.length, combined);
                    return;
                }
                rawTextInput.value = combined;
                updateTextCounters();

                const titleInput = document.getElementById('title');
                if (titleInput && !titleInput.value && data.title) {
                    titleInput.value = data.title;
                }
            } else {
                alert(data.message || 'No se pudo extraer texto reconocible de la imagen.');
            }
        })
        .catch(err => {
            console.error('OCR Error:', err);
            if (box) {
                box.classList.add('hidden');
                box.classList.remove('flex');
            }
            alert('Error conectando con el servicio de OCR.');
        });
    }

    // Global Paste Listener for Images (Ctrl+V with image screenshot)
    window.addEventListener('paste', (e) => {
        if (e.clipboardData && e.clipboardData.items) {
            for (let item of e.clipboardData.items) {
                if (item.type.indexOf('image') !== -1) {
                    const blob = item.getAsFile();
                    if (blob) {
                        e.preventDefault();
                        uploadAndOcrImage(blob);
                        return;
                    }
                }
            }
        }
    });

    // Click to open file dialog
    dropZone.addEventListener('click', () => fileInput.click());

    // Drag & Drop handlers
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.add('border-[#00ff87]', 'bg-[#00ff87]/10');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-[#00ff87]', 'bg-[#00ff87]/10');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            const fileName = files[0].name.toLowerCase();
            const validExts = ['.pdf', '.docx', '.doc', '.txt', '.md', '.markdown', '.png', '.jpg', '.jpeg', '.webp', '.bmp', '.mp3', '.wav', '.m4a', '.ogg'];
            const isValid = validExts.some(ext => fileName.endsWith(ext));
            if (isValid) {
                fileInput.files = files;
                updateFileInfo(files[0]);
            } else {
                alert('Formato no soportado. Puedes seleccionar PDF, DOCX, TXT, Markdown, Imágenes (PNG, JPG) o Audio (MP3, WAV).');
            }
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            updateFileInfo(fileInput.files[0]);
        }
    });

    function updateFileInfo(file) {
        selectedFileName.textContent = file.name;
        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
        selectedFileSize.textContent = `(${sizeMb} MB)`;
        fileSelectedBox.style.display = 'inline-flex';
        fileLabel.textContent = 'Archivo seleccionado correctamente';
    }

    // Submit state feedback & overlay activation
    uploadForm.addEventListener('submit', (e) => {
        if (currentInputMode === 'file') {
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert('Por favor selecciona un documento antes de comenzar.');
                return false;
            }
        } else {
            const textVal = rawTextInput ? rawTextInput.value.trim() : '';
            if (textVal.length < 10) {
                e.preventDefault();
                alert('Por favor ingresa o pega un texto con al menos 10 caracteres.');
                if (rawTextInput) rawTextInput.focus();
                return false;
            }
            if (textVal.length > MAX_TEXT_CHARS) {
                e.preventDefault();
                showTextLimitModal(textVal.length, textVal.length, textVal);
                return false;
            }
        }

        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        submitBtnText.textContent = 'Subiendo y preparando procesamiento...';

        if (submitOverlay) {
            submitOverlay.classList.remove('hidden');
            submitOverlay.classList.add('flex');
        }
    });

    // ──────────────────────────────────────────────────────────────────────────
    // Interactive Voice Preview Engine
    // ──────────────────────────────────────────────────────────────────────────
    let previewAudio = null;

    window.toggleVoicePreview = function() {
        const voiceSelect = document.getElementById('voice');
        if (!voiceSelect) return;
        const voiceId = voiceSelect.value;
        const btnText = document.getElementById('previewVoiceText');
        const btnIcon = document.getElementById('previewVoiceIcon');
        const playerContainer = document.getElementById('voicePreviewPlayerContainer');
        const statusText = document.getElementById('voicePreviewStatus');

        if (previewAudio && !previewAudio.paused) {
            previewAudio.pause();
            previewAudio.currentTime = 0;
            if (btnText) btnText.textContent = 'Escuchar muestra';
            if (btnIcon) btnIcon.textContent = '🔊';
            if (playerContainer) playerContainer.classList.add('hidden');
            return;
        }

        if (!previewAudio) {
            previewAudio = new Audio();
            previewAudio.addEventListener('ended', () => {
                if (btnText) btnText.textContent = 'Escuchar muestra';
                if (btnIcon) btnIcon.textContent = '🔊';
                if (playerContainer) playerContainer.classList.add('hidden');
            });
            previewAudio.addEventListener('error', (e) => {
                console.warn('Audio playback error:', e);
                if (btnText) btnText.textContent = 'Error al cargar';
                if (btnIcon) btnIcon.textContent = '⚠️';
                setTimeout(() => {
                    if (btnText) btnText.textContent = 'Escuchar muestra';
                    if (btnIcon) btnIcon.textContent = '🔊';
                    if (playerContainer) playerContainer.classList.add('hidden');
                }, 2500);
            });
        }

        const speedSelect = document.getElementById('speed_rate');
        const pitchSelect = document.getElementById('pitch');
        const speedVal = speedSelect ? speedSelect.value : '+0%';
        const pitchVal = pitchSelect ? pitchSelect.value : '+0Hz';

        const previewBaseUrl = "{{ route('voices.preview') }}";
        previewAudio.src = `${previewBaseUrl}?voice=${encodeURIComponent(voiceId)}&speed_rate=${encodeURIComponent(speedVal)}&pitch=${encodeURIComponent(pitchVal)}`;
        if (btnText) btnText.textContent = 'Detener muestra';
        if (btnIcon) btnIcon.textContent = '⏹️';
        if (statusText) statusText.textContent = `Reproduciendo: ${voiceSelect.options[voiceSelect.selectedIndex].text} (${speedVal}, ${pitchVal})`;
        if (playerContainer) playerContainer.classList.remove('hidden');

        previewAudio.play().catch(e => {
            console.warn('Playback play() was rejected or interrupted:', e);
            if (btnText) btnText.textContent = 'Escuchar muestra';
            if (btnIcon) btnIcon.textContent = '🔊';
            if (playerContainer) playerContainer.classList.add('hidden');
        });
    };

    window.onVoiceChanged = function() {
        if (previewAudio && !previewAudio.paused) {
            window.toggleVoicePreview();
        }
    };

    window.onVariantChanged = function() {
        if (previewAudio && !previewAudio.paused) {
            window.toggleVoicePreview();
        }
    };

    // ──────────────────────────────────────────────────────────────────────────
    // Reactive Masonic & Symbolic Content Detector
    // ──────────────────────────────────────────────────────────────────────────
    function checkMasonicPresence() {
        const textVal = rawTextInput ? rawTextInput.value : '';
        const fileVal = fileInput && fileInput.files.length > 0 ? fileInput.files[0].name : '';
        const titleInputEl = document.getElementById('title');
        const titleVal = titleInputEl ? titleInputEl.value : '';
        const combined = (textVal + ' ' + fileVal + ' ' + titleVal).toLowerCase();

        const hasMasonic = 
            /[∴]/.test(combined) ||
            /\.\s*·\s*\./.test(combined) ||
            /\.\.\s*\./.test(combined) ||
            /\b(gadu|s\.?f\.?u\.?|t\.?a\.?f\.?|l\.?i\.?f\.?|e\.?v\.?|a\.?l\.?|r\.?e\.?a\.?a\.?|i\.?p\.?h\.?|m\.?r\.?g\.?m\.?)\b/i.test(combined) ||
            /\b(a|l|g|d|u|i|ven|q|qq|h|hh|vvig|vig|vvisit|visit|s|f|e|v|m|mm|or|resp|log|secr|orad|tes|hosp|exp)\s*\(/i.test(combined) ||
            /[a-záéíóúñ]{1,4}\s*[:.·]{2,4}\s*[a-záéíóúñ]{1,4}\s*[:.·]{2,4}/i.test(combined) ||
            /\b(venerable\s+maestro|querido\s+hermano|gran\s+arquitecto|respetable\s+logia|plancha|taller|tenida|oriente\s+de|salud,\s*fuerza|primer\s+vigilante|segundo\s+vigilante|francmas|mas[oó]n|la\s+plomada)/i.test(combined);

        const masonicBox = document.getElementById('masonicNoticeBox');
        if (masonicBox) {
            if (hasMasonic) {
                masonicBox.classList.remove('hidden');
            } else {
                masonicBox.classList.add('hidden');
            }
        }
    }

    if (rawTextInput) {
        rawTextInput.addEventListener('input', checkMasonicPresence);
        rawTextInput.addEventListener('paste', () => setTimeout(checkMasonicPresence, 100));
    }
    const titleInput = document.getElementById('title');
    if (titleInput) {
        titleInput.addEventListener('input', checkMasonicPresence);
    }
    if (fileInput) {
        fileInput.addEventListener('change', checkMasonicPresence);
    }
    checkMasonicPresence();
</script>
@endpush
