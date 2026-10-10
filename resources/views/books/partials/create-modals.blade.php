<!-- Processing Loading Overlay (Instant feedback on submission) -->
<div id="submitOverlay" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex flex-col items-center justify-center p-4">
    <div class="card-tactile rounded-3xl p-6 sm:p-8 max-w-sm w-full text-center space-y-4 border border-indigo-500/30 shadow-2xl bg-white dark:bg-[#090d16]">
        <div class="relative w-16 h-16 mx-auto">
            <span class="absolute inset-0 rounded-full border-4 border-indigo-500/20 animate-pulse"></span>
            <svg class="w-16 h-16 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <div>
            <h3 class="text-base font-black text-slate-900 dark:text-white">Subiendo Documento</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1" id="overlayStatusMsg">Iniciando pipeline neuronal de extracción y voz...</p>
        </div>
        <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-2 w-full animate-pulse"></div>
        </div>
    </div>
</div>

<!-- Text Limit Warning & Guidance Modal (Direct Paste Limiter) -->
<div id="textLimitModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
    <div class="card-tactile rounded-3xl p-6 sm:p-7 max-w-lg w-full space-y-5 border border-amber-500/30 shadow-2xl relative bg-white dark:bg-[#090d16]">
        <!-- Modal Header -->
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center flex-shrink-0 text-amber-500 shadow-sm">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="space-y-1 min-w-0 flex-1">
                <h3 class="text-base font-black text-slate-900 dark:text-white">Límite de Pegado Directo Excedido</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
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
        <div class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 font-mono text-center">
            <div class="space-y-0.5">
                <span class="text-xs uppercase text-rose-500 font-bold block">Texto Detectado</span>
                <span id="modalDetectedChars" class="text-sm font-black text-rose-600 dark:text-rose-400">0 car.</span>
                <span id="modalDetectedWords" class="text-xs text-slate-500 dark:text-slate-400 block">(0 palabras)</span>
            </div>
            <div class="space-y-0.5 border-l border-slate-200 dark:border-slate-800">
                <span class="text-xs uppercase text-indigo-600 dark:text-indigo-400 font-bold block">Límite Permitido</span>
                <span class="text-sm font-black text-indigo-600 dark:text-indigo-400">50,000 car.</span>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">(~10,000 pal. / ~1h audio)</span>
            </div>
        </div>

        <!-- Explanatory recommendation -->
        <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300 leading-relaxed bg-indigo-50/50 dark:bg-slate-900/60 border border-indigo-500/20 p-3.5 rounded-2xl">
            <p class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                <span>💡 Recomendación para documentos extensos:</span>
            </p>
            <p>
                El pegado directo está diseñado para artículos breves, notas o capítulos individuales. Si deseas convertir un libro completo o tesis, te recomendamos <strong>subirlo como archivo</strong> (<span class="font-mono text-indigo-600 dark:text-indigo-400">.pdf, .docx, .txt</span>).
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Al subirlo como archivo, el sistema dividirá automáticamente todo el documento en pistas organizadas por capítulos sin riesgo de saturación ni truncado.
            </p>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2 pt-1">
            <button 
                type="button" 
                onclick="switchToUploadFromModal()"
                class="btn-primary-tactile px-4 py-2.5 rounded-xl text-xs font-black flex items-center justify-center gap-2 shadow-sm text-white"
            >
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

<!-- Universal Stylish Notification & Error Modal (Replaces browser alert()) -->
<div id="motaNoticeModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 transition-opacity">
    <div id="motaNoticeBox" class="card-tactile rounded-3xl p-6 sm:p-7 max-w-md w-full space-y-4 border border-rose-500/30 shadow-2xl relative transform transition-all duration-200 scale-95 opacity-0 bg-white dark:bg-[#090d16]">
        <!-- Modal Header -->
        <div class="flex items-start gap-3.5">
            <div id="motaNoticeIconBox" class="w-11 h-11 rounded-2xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-center flex-shrink-0 text-rose-500 shadow-sm">
                <span id="motaNoticeIcon" class="text-xl">⚠️</span>
            </div>
            <div class="space-y-1 min-w-0 flex-1">
                <h3 id="motaNoticeTitle" class="text-base font-black text-slate-900 dark:text-white">Aviso</h3>
                <p id="motaNoticeMessage" class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed"></p>
            </div>
            <button type="button" onclick="closeMotaNoticeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg transition" title="Cerrar ventana">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Optional Technical Details -->
        <div id="motaNoticeDetailContainer" class="hidden p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 font-mono text-xs text-slate-700 dark:text-slate-300 max-h-36 overflow-y-auto leading-relaxed break-words whitespace-pre-wrap">
            <span id="motaNoticeDetail"></span>
        </div>

        <!-- Modal Action Buttons -->
        <div class="flex items-center justify-end gap-2 pt-1">
            <button 
                type="button" 
                id="motaNoticeActionBtn"
                onclick="closeMotaNoticeModal()"
                class="w-full sm:w-auto px-5 py-2.5 btn-primary-tactile btn-neon-tactile rounded-xl text-xs font-black transition flex items-center justify-center gap-2 shadow-sm text-white"
            >
                <span>Entendido</span>
            </button>
        </div>
    </div>
</div>

<!-- Interactive Media Strategy & Storage Decision Modal -->
<div id="motaMediaStrategyModal" class="hidden fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-md overflow-y-auto p-3 sm:p-4 flex items-center justify-center transition-opacity">
    <div id="motaMediaStrategyBox" class="card-tactile rounded-2xl sm:rounded-3xl max-w-lg w-full max-h-[92dvh] sm:max-h-[88vh] flex flex-col relative transform transition-all duration-300 border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden my-auto bg-white dark:bg-[#090d16]">
        <!-- Modal Header (Fixed / Non-shrinking) -->
        <div class="p-4 sm:p-5 pb-3 border-b border-slate-200/80 dark:border-slate-800 flex items-start gap-3 shrink-0 bg-white/90 dark:bg-[#090d16]/90 backdrop-blur-sm">
            <div id="mediaModalIconBox" class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center flex-shrink-0 text-xl shadow-sm text-indigo-500">
                <span id="mediaModalIcon">🎙️</span>
            </div>
            <div class="space-y-0.5 min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <h3 id="mediaModalTitle" class="text-sm sm:text-base font-black text-slate-900 dark:text-white truncate">Archivo Multimedia Detectado</h3>
                    <span id="mediaModalTypeBadge" class="px-2 py-0.5 rounded-md text-[9px] font-mono font-bold bg-indigo-500/10 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400">Audio</span>
                </div>
                <p id="mediaModalSubtitle" class="text-xs text-slate-600 dark:text-slate-400 leading-snug">
                    Se detectó un archivo multimedia. Selecciona la estrategia deseada.
                </p>
            </div>
            <button type="button" onclick="closeMediaStrategyModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg transition" title="Cerrar ventana">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Body (Scrollable with custom scrollbar) -->
        <div class="overflow-y-auto flex-1 p-4 sm:p-5 space-y-4">
            <!-- Detected File Info Strip -->
            <div id="mediaModalInfoStrip" class="p-2.5 sm:p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs font-mono shadow-xs">
                <div class="flex items-center gap-2 truncate">
                    <span id="mediaModalMiniIcon" class="text-sm">🎬</span>
                    <span id="mediaModalFileName" class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[200px] sm:max-w-xs">archivo.mp4</span>
                </div>
                <span id="mediaModalFileSize" class="text-slate-500 dark:text-slate-400 font-bold shrink-0 text-[11px]">0 MB</span>
            </div>

            <!-- Strategy Selection Options -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    <span>1. ¿Cómo deseas procesar este contenido?</span>
                </label>
                <div class="space-y-2" id="mediaStrategyOptionsGroup">
                    <!-- Option 1: Transcribe STT + Auto-Correct + Synthesize TTS (Direct) -->
                    <label class="relative flex items-start gap-2.5 sm:gap-3 p-3 rounded-xl border border-indigo-500/30 bg-indigo-50/50 dark:bg-indigo-950/20 cursor-pointer hover:border-indigo-500 transition-all group">
                        <input type="radio" name="modal_strategy_option" value="stt_tts" checked class="mt-1 text-indigo-600 focus:ring-indigo-500 accent-indigo-600">
                        <div class="text-xs space-y-0.5 min-w-0 flex-1">
                            <strong class="text-slate-900 dark:text-white block font-bold flex items-center gap-1.5 flex-wrap">
                                <span>✨ Transcribir y Crear Audiolibro Directo</span>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">Auto-Corrección Ortográfica IA</span>
                            </strong>
                            <span class="text-[11px] text-slate-600 dark:text-slate-400 block leading-snug">
                                Extrae la voz, corrige tildes, homófonos y ortografía con IA y genera el audiolibro neuronal directamente con capítulos indexados.
                            </span>
                        </div>
                    </label>

                    <!-- Option 2: Transcribe STT + Auto-Correct + Open in Editor for Review (Recommended) -->
                    <label class="relative flex items-start gap-2.5 sm:gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 cursor-pointer hover:border-indigo-500 transition-all group">
                        <input type="radio" name="modal_strategy_option" value="stt_review_then_tts" class="mt-1 text-indigo-600 focus:ring-indigo-500 accent-indigo-600">
                        <div class="text-xs space-y-0.5 min-w-0 flex-1">
                            <strong class="text-slate-900 dark:text-white block font-bold flex items-center gap-1.5 flex-wrap">
                                <span>✏️ Transcribir, Auto-Corregir y Abrir en Editor</span>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Paso de Revisión Recomendado</span>
                            </strong>
                            <span class="text-[11px] text-slate-600 dark:text-slate-400 block leading-snug">
                                Transcribe y aplica corrección ortográfica, desplegando el texto en el editor para que puedas darle un vistazo o retocarlo antes de narrar.
                            </span>
                        </div>
                    </label>

                    <!-- Option 3: STT Only -->
                    <label class="relative flex items-start gap-2.5 sm:gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 cursor-pointer hover:border-indigo-500 transition-all group">
                        <input type="radio" name="modal_strategy_option" value="stt_only" class="mt-1 text-indigo-600 focus:ring-indigo-500 accent-indigo-600">
                        <div class="text-xs space-y-0.5 min-w-0 flex-1">
                            <strong class="text-slate-900 dark:text-white block font-bold flex items-center gap-1.5">
                                <span>🎙️ Solo Transcribir a Texto (STT Rápido)</span>
                            </strong>
                            <span class="text-[11px] text-slate-600 dark:text-slate-400 block leading-snug">
                                Extrae y corrige todo el texto de inmediato para lectura, edición o descarga en TXT/Markdown sin generar audio nuevo.
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Section 2: Storage & Retention Policy -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    <span>2. ¿Conservar el archivo multimedia original?</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="mediaKeepOptionsGroup">
                    <label class="relative flex items-start gap-2.5 p-3 rounded-xl border border-indigo-500/30 bg-indigo-50/50 dark:bg-indigo-950/20 cursor-pointer hover:border-indigo-500 transition group">
                        <input type="radio" name="modal_keep_media" value="0" checked class="mt-0.5 text-indigo-600 focus:ring-indigo-500 accent-indigo-600">
                        <div class="text-xs space-y-0.5 min-w-0 flex-1">
                            <strong class="text-slate-900 dark:text-white block font-bold text-[11px] sm:text-xs">
                                Desechar el archivo original tras transcribir
                            </strong>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block leading-tight">
                                Ahorro de almacenamiento. El audiolibro y texto quedan 100% disponibles.
                            </span>
                        </div>
                    </label>
                    <label class="relative flex items-start gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 cursor-pointer hover:border-indigo-500 transition group">
                        <input type="radio" name="modal_keep_media" value="1" class="mt-0.5 text-indigo-600 focus:ring-indigo-500 accent-indigo-600">
                        <div class="text-xs space-y-0.5 min-w-0 flex-1">
                            <strong class="text-slate-900 dark:text-white block font-bold text-[11px] sm:text-xs">
                                Conservar archivo multimedia original en el servidor
                            </strong>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block leading-tight">
                                Guardará el archivo en el disco del servidor.
                            </span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Modal Footer (Fixed / Non-shrinking) -->
        <div class="p-3.5 sm:p-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-end gap-2.5 shrink-0 bg-slate-50/90 dark:bg-[#090d16]/90 backdrop-blur-sm">
            <button 
                type="button" 
                onclick="closeMediaStrategyModal()"
                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition"
            >
                Cancelar
            </button>
            <button 
                type="button" 
                onclick="applyMediaStrategyDecision()"
                class="btn-primary-tactile px-5 py-2.5 rounded-xl text-xs font-black flex items-center gap-2 shadow-sm text-white"
            >
                <span>Aplicar y Continuar</span>
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>
    </div>
</div>
