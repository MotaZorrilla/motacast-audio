@extends('layouts.app')

@section('title', 'Agregar Documento o Libro - MotaCastAudio')

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
                Sube tu archivo PDF técnico, artículo o libro para convertirlo en audiolibro con voz neuronal natural.
            </p>
        </div>
    </div>

    <!-- User Quota or Guest Mode Notice -->
    @auth
        @if (!Auth::user()->isAdmin())
            <div class="p-3.5 rounded-xl bg-slate-100 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/50 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#00ff87]"></span>
                    <span class="text-slate-700 dark:text-sky-300">
                        Estado de tu cuota: <strong>{{ Auth::user()->books()->count() }}</strong> de {{ Auth::user()->book_limit === -1 ? 'Ilimitados' : Auth::user()->book_limit }} libros utilizados.
                    </span>
                </div>
                <span class="font-bold text-[#00c965] dark:text-[#00ff87]">
                    {{ Auth::user()->remainingBooks() === -1 ? 'Sin límite' : Auth::user()->remainingBooks() . ' restantes' }}
                </span>
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
                        <p class="text-sm font-bold text-slate-800 dark:text-cyan-200" id="fileLabel">Toca para seleccionar o arrastra tu archivo aquí</p>
                        <p class="text-xs text-slate-500 dark:text-sky-400/80 mt-1">PDF, DOCX, TXT, MD, Imágenes (PNG, JPG) o Audio (MP3, WAV) — hasta 100 MB</p>
                        <div class="flex items-center justify-center gap-2 mt-2 flex-wrap">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">PDF</span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800/40">DOCX</span>
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
                <div class="flex items-center gap-3">
                    <span><strong id="counterWords" class="text-slate-800 dark:text-cyan-200 font-bold">0</strong> palabras</span>
                    <span><strong id="counterChars" class="text-slate-800 dark:text-cyan-200 font-bold">0</strong> caracteres</span>
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
                    <label for="voice" class="block text-xs font-semibold text-slate-700 dark:text-sky-300 mb-1.5">
                        Voz Narradora
                    </label>
                    <select 
                        name="voice" 
                        id="voice" 
                        class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/50 rounded-xl text-slate-900 dark:text-cyan-200 focus:outline-none focus:ring-2 focus:ring-[#00ff87] transition"
                    >
                        @foreach ($voices as $v)
                            <option value="{{ $v['id'] }}" {{ $v['recommended'] ? 'selected' : '' }}>
                                {{ $v['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Speed / Rate -->
                <div>
                    <label for="speed_rate" class="block text-xs font-semibold text-slate-700 dark:text-sky-300 mb-1.5">
                        Velocidad
                    </label>
                    <select 
                        name="speed_rate" 
                        id="speed_rate" 
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
        const text = rawTextInput.value.trim();
        const chars = text.length;
        const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
        const minutes = Math.max(1, Math.ceil(words / 150)); // ~150 words per minute speaking rate

        if (counterWords) counterWords.textContent = words.toLocaleString();
        if (counterChars) counterChars.textContent = chars.toLocaleString();
        if (counterEstTime) {
            counterEstTime.textContent = words > 0 ? `~${minutes} min de audio` : '~0 min de audio';
        }
    }

    if (rawTextInput) {
        rawTextInput.addEventListener('input', updateTextCounters);
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
                rawTextInput.value = text;
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
                rawTextInput.value = prev ? (prev + "\n\n" + data.text) : data.text;
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
        }

        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        submitBtnText.textContent = 'Subiendo y preparando procesamiento...';

        if (submitOverlay) {
            submitOverlay.classList.remove('hidden');
            submitOverlay.classList.add('flex');
        }
    });
</script>
@endpush
