<!-- Intake Mode Tabs: File Upload vs Direct Text Paste -->
<div>
    <div class="flex items-center p-1 rounded-2xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 mb-3 shadow-inner">
        <button 
            type="button" 
            id="tabModeFile" 
            onclick="switchInputMode('file')"
            class="flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition bg-white dark:bg-slate-800 text-slate-900 dark:text-indigo-400 shadow-sm border border-slate-200 dark:border-slate-700"
        >
            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <span>Subir Documento</span>
            <span class="hidden md:inline text-xs text-slate-400 font-normal">(PDF, DOCX, TXT, MD)</span>
        </button>
        <button 
            type="button" 
            id="tabModeText" 
            onclick="switchInputMode('text')"
            class="flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white"
        >
            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span>Pegar Texto Directo</span>
            <span class="px-1.5 py-0.5 rounded-md text-[9px] font-mono font-black bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">NUEVO</span>
        </button>
    </div>
    <input type="hidden" name="input_mode" id="inputModeHidden" value="file">
    <input type="hidden" name="keep_original_media" id="keepOriginalMediaHidden" value="0">
</div>

<!-- Section 1: File Drag & Drop Zone -->
<div id="sectionFileDrop">
    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
        Documento, Imagen, Audio o Video a convertir <span class="text-rose-500">*</span>
    </label>
    <div 
        id="dropZone"
        class="border-2 border-dashed border-slate-300 dark:border-slate-800 hover:border-indigo-500 rounded-2xl p-8 text-center transition cursor-pointer bg-slate-50/70 dark:bg-slate-950/60 hover:bg-indigo-500/5 group"
    >
        <input type="file" name="pdf_file" id="pdfFileInput" accept=".pdf,.docx,.doc,.txt,.md,.markdown,.png,.jpg,.jpeg,.webp,.bmp,.mp3,.wav,.m4a,.ogg,.aac,.flac,.mp4,.mkv,.mov,.avi,.webm" class="hidden">
        
        <div class="flex flex-col items-center justify-center space-y-3">
            <div id="dropZoneIconBox" class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 shadow-sm flex items-center justify-center text-indigo-600 dark:text-indigo-400 group-hover:scale-105 group-hover:border-indigo-500 transition">
                <span id="dropZoneIcon">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                </span>
            </div>
            <div class="text-center">
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Word (.docx, .doc), PDF, TXT, MD, Fotos (PNG, JPG), Audio (MP3, WAV) o Video (MP4, MKV) — hasta 100 MB</p>
                <div class="flex items-center justify-center gap-2 mt-2 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800/40">WORD (.DOCX, .DOC)</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">PDF</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800/60 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700/40">TXT/MD</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">📸 IMG (OCR)</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">🎙️ AUDIO (STT)</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/40">🎬 VIDEO (MP4, MKV)</span>
                </div>
            </div>
        </div>

        <!-- Selected File Indicator (hidden by default) -->
        <div id="fileSelectedBox" style="display: none;" class="mt-4 inline-flex items-center gap-2.5 px-3.5 py-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 rounded-xl text-xs font-semibold shadow-sm transition-all">
            <span id="selectedFileIcon" class="text-base flex-shrink-0">📄</span>
            <span id="selectedFileName" class="font-bold truncate max-w-xs">archivo.pdf</span>
            <span id="selectedFileTypeBadge" class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30">PDF</span>
            <span id="selectedFileSize" class="text-slate-500 dark:text-slate-400 font-mono text-[11px]"></span>
        </div>
    </div>
</div>

<!-- Section 2: Direct Text Area (Hidden by default) -->
<div id="sectionRawText" class="hidden space-y-2">
    <div class="flex items-center justify-between">
        <label for="rawTextInput" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            Texto a convertir en voz <span class="text-rose-500">*</span>
        </label>
        <div class="flex items-center gap-2 flex-wrap">
            <button 
                type="button" 
                onclick="document.getElementById('ocrImageInput').click()"
                class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:border-indigo-500 transition flex items-center gap-1 shadow-sm"
                title="Extraer texto de una imagen (también puedes pegar con Ctrl+V)"
            >
                <span>📸 OCR Imagen</span>
            </button>
            <input type="file" id="ocrImageInput" accept="image/*" class="hidden" onchange="handleOcrImageUpload(this)">

            <button 
                type="button" 
                onclick="document.getElementById('sttAudioInput').click()"
                class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-300 dark:border-indigo-800/60 hover:bg-indigo-500/20 transition flex items-center gap-1 shadow-sm"
                title="Transcribir archivo de audio o video directamente a texto"
            >
                <span>🎙️ Audio / Video a Texto (STT)</span>
            </button>
            <input type="file" id="sttAudioInput" accept="audio/*,video/*,.mp3,.wav,.m4a,.ogg,.aac,.flac,.mp4,.mkv,.mov,.avi,.webm" class="hidden" onchange="handleSttAudioUpload(this)">

            <button 
                type="button" 
                onclick="pasteFromClipboard()"
                class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:border-indigo-500 transition flex items-center gap-1 shadow-sm"
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
                class="px-2 py-1 text-xs font-medium text-slate-400 hover:text-rose-500 transition"
            >
                Limpiar
            </button>
        </div>
    </div>

    <!-- OCR Extraction Status Indicator (Hidden by default) -->
    <div id="ocrStatusBox" class="hidden p-2.5 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-800 dark:text-indigo-300 text-xs font-semibold items-center gap-2">
        <svg class="w-4 h-4 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span id="ocrStatusText">Extrayendo texto de la imagen con OCR...</span>
    </div>

    <!-- STT Audio Transcription Status Indicator (Hidden by default) -->
    <div id="sttStatusBox" class="hidden p-2.5 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-800 dark:text-indigo-300 text-xs font-semibold items-center gap-2">
        <svg class="w-4 h-4 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span id="sttStatusText">Transcribiendo audio a texto con IA...</span>
    </div>

    <textarea 
        name="raw_text" 
        id="rawTextInput" 
        rows="9"
        placeholder="Pega aquí el artículo, ensayo, notas o presiona Ctrl+V para pegar capturas de pantalla/imágenes con OCR automático...&#10;&#10;Consejo: Puedes separar secciones con doble salto de línea o encabezados (# Título) para crear capítulos separados automáticamente."
        class="w-full px-4 py-3 text-xs sm:text-sm bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-2xl text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono leading-relaxed transition resize-y"
    >{{ old('raw_text') }}</textarea>

    <!-- Text Metrics & Dynamic Listening Estimation -->
    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-mono px-1">
        <div class="flex items-center gap-3 flex-wrap">
            <span><strong id="counterWords" class="text-slate-800 dark:text-white font-bold">0</strong> palabras</span>
            <span><strong id="counterChars" class="text-slate-800 dark:text-white font-bold">0</strong> / 50,000 car.</span>
            <span id="counterLimitWarning" class="hidden text-rose-500 font-bold text-[10px] animate-pulse">⚠️ Límite de 50k superado</span>
        </div>
        <div class="flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400 font-bold">
            <span>⏱️ Estimado:</span>
            <span id="counterEstTime">~0 min de audio</span>
        </div>
    </div>
</div>
