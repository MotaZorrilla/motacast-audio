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

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // Custom Tactile Cyberpunk Notice Modal (Replaces browser alert())
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function showNoticeModal({ title = 'Aviso', message = '', type = 'error', detail = null, onConfirm = null }) {
        const modal = document.getElementById('motaNoticeModal');
        const box = document.getElementById('motaNoticeBox');
        const iconBox = document.getElementById('motaNoticeIconBox');
        const icon = document.getElementById('motaNoticeIcon');
        const titleEl = document.getElementById('motaNoticeTitle');
        const msgEl = document.getElementById('motaNoticeMessage');
        const detailContainer = document.getElementById('motaNoticeDetailContainer');
        const detailEl = document.getElementById('motaNoticeDetail');
        const actionBtn = document.getElementById('motaNoticeActionBtn');

        if (!modal || !box) return;

        titleEl.textContent = title;
        msgEl.textContent = message;

        if (detail && String(detail).trim()) {
            detailEl.textContent = String(detail).trim();
            detailContainer.classList.remove('hidden');
        } else {
            detailContainer.classList.add('hidden');
        }

        // Reset base classes
        box.className = "card-tactile rounded-3xl p-6 sm:p-7 max-w-md w-full space-y-4 border shadow-2xl relative transform transition-all duration-200";
        iconBox.className = "w-11 h-11 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm border";

        if (type === 'error') {
            box.classList.add('border-rose-500/50', 'shadow-[0_0_25px_rgba(244,63,94,0.15)]');
            iconBox.classList.add('bg-rose-500/10', 'border-rose-500/30', 'text-rose-500');
            icon.textContent = 'âŒ';
        } else if (type === 'warning') {
            box.classList.add('border-amber-500/50', 'shadow-[0_0_25px_rgba(245,158,11,0.15)]');
            iconBox.classList.add('bg-amber-500/10', 'border-amber-500/30', 'text-amber-500');
            icon.textContent = 'âš ï¸';
        } else if (type === 'success') {
            box.classList.add('border-[#00ff87]/50', 'shadow-[0_0_25px_rgba(0,255,135,0.15)]');
            iconBox.classList.add('bg-[#00ff87]/10', 'border-[#00ff87]/30', 'text-[#00ff87]');
            icon.textContent = 'âœ…';
        } else { // info
            box.classList.add('border-cyan-500/50', 'shadow-[0_0_25px_rgba(6,182,212,0.15)]');
            iconBox.classList.add('bg-cyan-500/10', 'border-cyan-500/30', 'text-cyan-400');
            icon.textContent = 'â„¹ï¸';
        }

        actionBtn.onclick = function() {
            closeMotaNoticeModal();
            if (typeof onConfirm === 'function') onConfirm();
        };

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            box.classList.remove('scale-95', 'opacity-0');
            box.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeMotaNoticeModal() {
        const modal = document.getElementById('motaNoticeModal');
        const box = document.getElementById('motaNoticeBox');
        if (!modal || !box) return;

        box.classList.remove('scale-100', 'opacity-100');
        box.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 150);
    }

    // Keyboard support: Escape closes modals
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const noticeModal = document.getElementById('motaNoticeModal');
            if (noticeModal && !noticeModal.classList.contains('hidden')) {
                closeMotaNoticeModal();
            }
            const limitModal = document.getElementById('textLimitModal');
            if (limitModal && !limitModal.classList.contains('hidden')) {
                closeTextLimitModal();
            }
            const mediaModal = document.getElementById('motaMediaStrategyModal');
            if (mediaModal && !mediaModal.classList.contains('hidden')) {
                closeMediaStrategyModal();
            }
        }
    });

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
        const stepVoiceSection = document.getElementById('stepVoiceSection');
        const stepAudioSection = document.getElementById('stepAudioSection');
        const stepSubmitSection = document.getElementById('stepSubmitSection');

        if (mode === 'file') {
            sectionFileDrop.classList.remove('hidden');
            sectionRawText.classList.add('hidden');

            tabModeFile.className = "flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition bg-white dark:bg-[#0d1c22] text-slate-900 dark:text-[#00ff87] shadow-sm border border-slate-200 dark:border-cyan-800/60";
            tabModeText.className = "flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition text-slate-500 dark:text-sky-400 hover:text-slate-900 dark:hover:text-cyan-200";

            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                updateFileInfo(fileInput.files[0]);
            } else {
                if (stepVoiceSection) stepVoiceSection.classList.add('hidden');
                if (stepAudioSection) stepAudioSection.classList.add('hidden');
                if (stepSubmitSection) stepSubmitSection.classList.add('hidden');
            }
        } else {
            sectionFileDrop.classList.add('hidden');
            sectionRawText.classList.remove('hidden');

            tabModeText.className = "flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition bg-white dark:bg-[#0d1c22] text-slate-900 dark:text-[#00ff87] shadow-sm border border-slate-200 dark:border-cyan-800/60";
            tabModeFile.className = "flex-1 py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition text-slate-500 dark:text-sky-400 hover:text-slate-900 dark:hover:text-cyan-200";

            if (stepAudioSection) stepAudioSection.classList.add('hidden');

            const hasText = rawTextInput && rawTextInput.value.trim().length >= 10;
            if (hasText) {
                if (stepVoiceSection) stepVoiceSection.classList.remove('hidden');
                if (stepSubmitSection) stepSubmitSection.classList.remove('hidden');
                if (submitBtnText) submitBtnText.textContent = '🚀 Comenzar a Crear Audiolibro';
            } else {
                if (stepVoiceSection) stepVoiceSection.classList.add('hidden');
                if (stepSubmitSection) stepSubmitSection.classList.add('hidden');
            }

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

        if (currentInputMode === 'text') {
            const stepVoiceSection = document.getElementById('stepVoiceSection');
            const stepSubmitSection = document.getElementById('stepSubmitSection');
            if (chars >= 10) {
                if (stepVoiceSection) stepVoiceSection.classList.remove('hidden');
                if (stepSubmitSection) stepSubmitSection.classList.remove('hidden');
                if (submitBtnText) submitBtnText.textContent = '🚀 Comenzar a Crear Audiolibro';
            } else {
                if (stepVoiceSection) stepVoiceSection.classList.add('hidden');
                if (stepSubmitSection) stepSubmitSection.classList.add('hidden');
            }
        }

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
                showNoticeModal({
                    title: 'Acceso a Portapapeles',
                    message: 'Tu navegador no permite lectura directa del portapapeles. Por favor presiona Ctrl+V en el área de texto.',
                    type: 'info'
                });
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
                showNoticeModal({
                    title: 'Portapapeles de Texto Vacío',
                    message: 'No se detectó texto en el portapapeles. Si copiaste una imagen o captura de pantalla, puedes usar el botón «📸 OCR Imagen» o pulsar Ctrl+V.',
                    type: 'warning'
                });
            }
        } catch (err) {
            console.warn('Clipboard read error:', err);
            showNoticeModal({
                title: 'Permiso del Portapapeles',
                message: 'No se pudo leer el portapapeles directamente. Por favor presiona Ctrl+V manualmente en el área de texto.',
                type: 'info'
            });
        }
    }

    function clearRawText() {
        if (rawTextInput) {
            rawTextInput.value = '';
            updateTextCounters();
            const stepVoiceSection = document.getElementById('stepVoiceSection');
            const stepSubmitSection = document.getElementById('stepSubmitSection');
            if (stepVoiceSection) stepVoiceSection.classList.add('hidden');
            if (stepSubmitSection) stepSubmitSection.classList.add('hidden');
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
                showNoticeModal({
                    title: 'Incidencia en ExtracciÃ³n OCR',
                    message: data.message || 'No se pudo extraer texto reconocible de la imagen.',
                    detail: data.detail || null,
                    type: 'warning'
                });
            }
        })
        .catch(err => {
            console.error('OCR Error:', err);
            if (box) {
                box.classList.add('hidden');
                box.classList.remove('flex');
            }
            showNoticeModal({
                title: 'Error de Comunicación',
                message: 'Ocurrió un error al enviar la imagen al servicio de OCR. Por favor verifica tu conexión o intenta con otra imagen.',
                detail: err ? err.message : null,
                type: 'error'
            });
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Audio/Video-to-Text (STT) Speech Transcription Client Engine & Dynamic Step UX
    // ──────────────────────────────────────────────────────────────────────────
    let currentDetectedType = 'text'; // 'text' | 'audio'
    let currentAudioActionMode = 'stt_and_tts'; // 'stt_only' | 'stt_and_tts'
    let currentMediaKind = 'audio'; // 'audio' | 'video'

    // Interactive Media Strategy Modal Handlers
    function openMediaStrategyModal(file, mediaKind = 'audio') {
        currentMediaKind = mediaKind;
        const modal = document.getElementById('motaMediaStrategyModal');
        const box = document.getElementById('motaMediaStrategyBox');
        const iconBox = document.getElementById('mediaModalIconBox');
        const iconEl = document.getElementById('mediaModalIcon');
        const miniIconEl = document.getElementById('mediaModalMiniIcon');
        const badgeEl = document.getElementById('mediaModalTypeBadge');
        const titleEl = document.getElementById('mediaModalTitle');
        const subtitleEl = document.getElementById('mediaModalSubtitle');
        const nameEl = document.getElementById('mediaModalFileName');
        const sizeEl = document.getElementById('mediaModalFileSize');

        if (!modal) return;

        if (mediaKind === 'video') {
            if (iconEl) iconEl.textContent = '🎬';
            if (miniIconEl) miniIconEl.textContent = '🎬';
            if (badgeEl) {
                badgeEl.textContent = 'Video';
                badgeEl.className = 'px-2 py-0.5 rounded-md text-[9px] font-mono font-bold bg-purple-500/20 border border-purple-500/40 text-purple-700 dark:text-purple-300 shadow-[0_0_10px_rgba(168,85,247,0.2)]';
            }
            if (titleEl) titleEl.textContent = 'Archivo de Video Detectado';
            if (subtitleEl) subtitleEl.textContent = 'Se detectó un video multimedia. Extraeremos su pista de audio con IA para convertirlo en audiolibro neuronal o texto.';
            if (box) {
                box.className = "card-tactile rounded-3xl p-6 sm:p-7 max-w-lg w-full space-y-5 border border-purple-500/40 shadow-[0_0_35px_rgba(168,85,247,0.2)] relative transform transition-all duration-300";
            }
            if (iconBox) {
                iconBox.className = "w-12 h-12 rounded-2xl bg-purple-500/15 border border-purple-500/30 flex items-center justify-center flex-shrink-0 text-2xl shadow-sm text-purple-400";
            }
        } else {
            if (iconEl) iconEl.textContent = '🎙️';
            if (miniIconEl) miniIconEl.textContent = '🎙️';
            if (badgeEl) {
                badgeEl.textContent = 'Audio';
                badgeEl.className = 'px-2 py-0.5 rounded-md text-[9px] font-mono font-bold bg-[#00ff87]/20 border border-[#00ff87]/40 text-[#00c965] dark:text-[#00ff87] shadow-[0_0_10px_rgba(0,255,135,0.2)]';
            }
            if (titleEl) titleEl.textContent = 'Archivo de Audio Detectado';
            if (subtitleEl) subtitleEl.textContent = 'Se detectó una grabación de voz. Selecciona la estrategia de procesamiento y retención deseada.';
            if (box) {
                box.className = "card-tactile rounded-3xl p-6 sm:p-7 max-w-lg w-full space-y-5 border border-[#00ff87]/40 shadow-[0_0_35px_rgba(0,255,135,0.2)] relative transform transition-all duration-300";
            }
            if (iconBox) {
                iconBox.className = "w-12 h-12 rounded-2xl bg-[#00ff87]/15 border border-[#00ff87]/30 flex items-center justify-center flex-shrink-0 text-2xl shadow-sm text-[#00ff87]";
            }
        }

        if (nameEl && file) nameEl.textContent = file.name;
        if (sizeEl && file) {
            const mb = (file.size / (1024 * 1024)).toFixed(2);
            sizeEl.textContent = `${mb} MB`;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeMediaStrategyModal() {
        const modal = document.getElementById('motaMediaStrategyModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function applyMediaStrategyDecision() {
        const strategyRadio = document.querySelector('input[name="modal_strategy_option"]:checked');
        const keepRadio = document.querySelector('input[name="modal_keep_media"]:checked');
        const strategy = strategyRadio ? strategyRadio.value : 'stt_tts';
        const keep = keepRadio ? keepRadio.value : '0';

        const keepInput = document.getElementById('keepOriginalMediaHidden');
        if (keepInput) keepInput.value = keep;

        const stepVoiceSection = document.getElementById('stepVoiceSection');
        const stepAudioSection = document.getElementById('stepAudioSection');
        const stepSubmitSection = document.getElementById('stepSubmitSection');
        const submitBtnText = document.getElementById('submitBtnText');

        if (strategy === 'stt_review_then_tts') {
            closeMediaStrategyModal();
            switchInputMode('text');
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                uploadAndTranscribeAudio(fileInput.files[0], 'sttStatusBox', 'sttStatusText', (data) => {
                    if (stepVoiceSection) stepVoiceSection.classList.remove('hidden');
                    if (stepAudioSection) stepAudioSection.classList.add('hidden');
                    if (stepSubmitSection) stepSubmitSection.classList.remove('hidden');
                    if (submitBtnText) submitBtnText.textContent = '🚀 Generar Audiolibro Neuronal con este Texto';
                    showNoticeModal({
                        title: '¡Transcripción y Auto-Corrección Listas!',
                        message: `Se transcribieron ${data.words || 0} palabras con auto-corrección ortográfica y puntuación. Puedes leer el texto en el editor, afinar cualquier detalle y elegir la voz neuronal abajo para crear tu audiolibro.`,
                        type: 'success'
                    });
                });
            }
            return;
        }

        if (strategy === 'stt_tts') {
            currentAudioActionMode = 'stt_and_tts';
            if (stepVoiceSection) stepVoiceSection.classList.remove('hidden');
            if (stepAudioSection) stepAudioSection.classList.add('hidden');
            if (stepSubmitSection) stepSubmitSection.classList.remove('hidden');
            if (submitBtnText) submitBtnText.textContent = '✨ Transcribir y Crear Audiolibro Neuronal';
        } else {
            currentAudioActionMode = 'stt_only';
            if (stepVoiceSection) stepVoiceSection.classList.add('hidden');
            if (stepAudioSection) stepAudioSection.classList.remove('hidden');
            if (stepSubmitSection) stepSubmitSection.classList.remove('hidden');
            if (submitBtnText) {
                submitBtnText.textContent = (currentMediaKind === 'video')
                    ? '🎬 Transcribir Video a Texto (STT)'
                    : '🎙️ Transcribir Audio a Texto (STT)';
            }
        }

        closeMediaStrategyModal();
    }

    function setDetectedContentType(type, file = null, mediaKind = 'audio') {
        currentDetectedType = type;
        currentMediaKind = mediaKind;
        const stepVoiceSection = document.getElementById('stepVoiceSection');
        const stepAudioSection = document.getElementById('stepAudioSection');
        const stepSubmitSection = document.getElementById('stepSubmitSection');
        const submitBtnText = document.getElementById('submitBtnText');

        if (type === 'audio' && file) {
            const nameEl = document.getElementById('audioFileNameDisplay');
            const sizeEl = document.getElementById('audioFileSizeDisplay');
            if (nameEl) nameEl.textContent = file.name;
            if (sizeEl) {
                const mb = (file.size / (1024 * 1024)).toFixed(2);
                sizeEl.textContent = (mediaKind === 'video')
                    ? `Archivo de video detectado (${mb} MB)`
                    : `Archivo de audio detectado (${mb} MB)`;
            }

            if (currentAudioActionMode === 'stt_and_tts') {
                if (stepVoiceSection) stepVoiceSection.classList.remove('hidden');
                if (stepAudioSection) stepAudioSection.classList.add('hidden');
                if (stepSubmitSection) stepSubmitSection.classList.remove('hidden');
                if (submitBtnText) submitBtnText.textContent = '✨ Transcribir y Crear Audiolibro Neuronal';
            } else {
                if (stepVoiceSection) stepVoiceSection.classList.add('hidden');
                if (stepAudioSection) stepAudioSection.classList.remove('hidden');
                if (stepSubmitSection) stepSubmitSection.classList.remove('hidden');
                if (submitBtnText) {
                    submitBtnText.textContent = (mediaKind === 'video')
                        ? '🎬 Transcribir Video a Texto (STT)'
                        : '🎙️ Transcribir Audio a Texto (STT)';
                }
            }
        } else if (file) {
            if (stepVoiceSection) stepVoiceSection.classList.remove('hidden');
            if (stepAudioSection) stepAudioSection.classList.add('hidden');
            if (stepSubmitSection) stepSubmitSection.classList.remove('hidden');

            if (submitBtnText) {
                submitBtnText.textContent = '🚀 Comenzar a Crear Audiolibro';
            }
        } else {
            // Initial state: no file and not enough text
            if (stepVoiceSection) stepVoiceSection.classList.add('hidden');
            if (stepAudioSection) stepAudioSection.classList.add('hidden');
            if (stepSubmitSection) stepSubmitSection.classList.add('hidden');
        }
    }

    function onAudioActionModeChanged(mode) {
        currentAudioActionMode = mode;
        const voiceContainer = document.getElementById('audioTtsVoiceContainer');
        const submitBtnText = document.getElementById('submitBtnText');

        if (mode === 'stt_and_tts') {
            if (voiceContainer) voiceContainer.classList.remove('hidden');
            if (submitBtnText) submitBtnText.textContent = '⚡ Transcribir y Sintetizar Audiolibro';
        } else {
            if (voiceContainer) voiceContainer.classList.add('hidden');
            if (submitBtnText) submitBtnText.textContent = '🎙️ Transcribir Audio a Texto (STT)';
        }
    }

    function syncAudioVoice(val) {
        const mainVoice = document.getElementById('voice');
        if (mainVoice) mainVoice.value = val;
    }

    function triggerDirectSttFromSelectedAudio() {
        if (!fileInput.files || fileInput.files.length === 0) {
            showNoticeModal({
                title: 'Audio Requerido',
                message: 'Por favor selecciona o arrastra un archivo de audio para transcribir.',
                type: 'warning'
            });
            return;
        }
        uploadAndTranscribeAudio(fileInput.files[0], 'directSttStatusBox', 'directSttStatusText');
    }

    function handleSttAudioUpload(input) {
        if (input.files && input.files.length > 0) {
            uploadAndTranscribeAudio(input.files[0], 'sttStatusBox', 'sttStatusText');
            input.value = '';
        }
    }

    function uploadAndTranscribeAudio(file, customBoxId = null, customLabelId = null, onSuccessCallback = null) {
        const boxId = customBoxId || 'sttStatusBox';
        const labelId = customLabelId || 'sttStatusText';
        const box = document.getElementById(boxId);
        const textLabel = document.getElementById(labelId);
        const btnTranscribe = document.getElementById('btnTranscribeAudioNow');

        if (btnTranscribe) {
            btnTranscribe.disabled = true;
            btnTranscribe.classList.add('opacity-60', 'cursor-not-allowed');
        }
        if (box) {
            box.classList.remove('hidden');
            box.classList.add('flex');
        }
        if (textLabel) textLabel.textContent = `Transcribiendo voz del audio "${file.name || 'grabación'}" con IA...`;

        const formData = new FormData();
        formData.append('audio', file);

        fetch("{{ route('books.stt.preview') }}", {
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
            if (btnTranscribe) {
                btnTranscribe.disabled = false;
                btnTranscribe.classList.remove('opacity-60', 'cursor-not-allowed');
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

                if (typeof onSuccessCallback === 'function') {
                    onSuccessCallback(data);
                } else {
                    showNoticeModal({
                        title: '¡Transcripción Exitosa!',
                        message: `Se transcribieron ${data.words || 0} palabras del audio. Ya tienes el texto disponible en el área de texto para leerlo, exportarlo o convertirlo en audiolibro.`,
                        type: 'success'
                    });
                }
            } else {
                showNoticeModal({
                    title: 'Incidencia en Transcripción de Audio',
                    message: data.message || 'No se pudo transcribir voz comprensible del audio.',
                    detail: data.detail || null,
                    type: 'warning'
                });
            }
        })
        .catch(err => {
            console.error('STT Error:', err);
            if (box) {
                box.classList.add('hidden');
                box.classList.remove('flex');
            }
            if (btnTranscribe) {
                btnTranscribe.disabled = false;
                btnTranscribe.classList.remove('opacity-60', 'cursor-not-allowed');
            }
            showNoticeModal({
                title: 'Error de Comunicación',
                message: 'Ocurrió un error al procesar el archivo de audio. Por favor verifica el formato y duración del audio.',
                detail: err ? err.message : null,
                type: 'error'
            });
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
            const validExts = ['.pdf', '.docx', '.doc', '.txt', '.md', '.markdown', '.png', '.jpg', '.jpeg', '.webp', '.bmp', '.mp3', '.wav', '.m4a', '.ogg', '.aac', '.flac', '.mp4', '.mkv', '.mov', '.avi', '.webm'];
            const isValid = validExts.some(ext => fileName.endsWith(ext));
            if (isValid) {
                fileInput.files = files;
                updateFileInfo(files[0]);
            } else {
                showNoticeModal({
                    title: 'Formato no compatible',
                    message: 'El archivo arrastrado no es compatible. Puedes subir documentos Word (.docx, .doc), PDF, TXT, Markdown, Imágenes (PNG, JPG, WEBP), Audio (MP3, WAV) o Video (MP4, MKV).',
                    type: 'warning'
                });
            }
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            updateFileInfo(fileInput.files[0]);
        }
    });

    function updateFileInfo(file) {
        if (!file) return;
        if (selectedFileName) selectedFileName.textContent = file.name;
        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
        if (selectedFileSize) selectedFileSize.textContent = `(${sizeMb} MB)`;
        if (fileSelectedBox) fileSelectedBox.style.display = 'inline-flex';
        if (fileLabel) fileLabel.textContent = 'Archivo seleccionado correctamente';

        const nameLower = file.name.toLowerCase();
        const audioExts = ['.mp3', '.wav', '.m4a', '.ogg', '.aac', '.flac'];
        const videoExts = ['.mp4', '.mkv', '.mov', '.avi', '.webm'];
        const imgExts = ['.png', '.jpg', '.jpeg', '.webp', '.bmp'];
        const isAudio = audioExts.some(ext => nameLower.endsWith(ext));
        const isVideo = videoExts.some(ext => nameLower.endsWith(ext));
        const isImg = imgExts.some(ext => nameLower.endsWith(ext));
        const isDocx = nameLower.endsWith('.docx') || nameLower.endsWith('.doc');

        const iconEl = document.getElementById('selectedFileIcon');
        const badgeEl = document.getElementById('selectedFileTypeBadge');
        const dropIconBox = document.getElementById('dropZoneIconBox');

        if (isVideo) {
            if (iconEl) iconEl.textContent = '🎬';
            if (badgeEl) {
                badgeEl.textContent = 'VIDEO';
                badgeEl.className = 'px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-500/40 shadow-[0_0_10px_rgba(168,85,247,0.2)]';
            }
            if (fileSelectedBox) {
                fileSelectedBox.className = 'mt-4 inline-flex items-center gap-2.5 px-3.5 py-2 bg-purple-500/10 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-500/40 rounded-xl text-xs font-semibold shadow-[0_0_20px_rgba(168,85,247,0.15)] transition-all';
            }
            if (dropIconBox) {
                dropIconBox.className = 'w-14 h-14 rounded-2xl bg-purple-500/10 dark:bg-purple-950/60 border border-purple-500/40 shadow-[0_0_20px_rgba(168,85,247,0.2)] flex items-center justify-center text-purple-400 group-hover:scale-105 transition';
            }
        } else if (isAudio) {
            if (iconEl) iconEl.textContent = '🎙️';
            if (badgeEl) {
                badgeEl.textContent = 'AUDIO';
                badgeEl.className = 'px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-[#00ff87]/20 text-[#00c965] dark:text-[#00ff87] border border-[#00ff87]/40 shadow-[0_0_10px_rgba(0,255,135,0.2)]';
            }
            if (fileSelectedBox) {
                fileSelectedBox.className = 'mt-4 inline-flex items-center gap-2.5 px-3.5 py-2 bg-[#00ff87]/10 dark:bg-cyan-950/60 text-[#00c965] dark:text-[#00ff87] border border-[#00ff87]/40 rounded-xl text-xs font-semibold shadow-[0_0_20px_rgba(0,255,135,0.15)] transition-all';
            }
            if (dropIconBox) {
                dropIconBox.className = 'w-14 h-14 rounded-2xl bg-emerald-500/10 dark:bg-[#00ff87]/10 border border-[#00ff87]/40 shadow-[0_0_20px_rgba(0,255,135,0.2)] flex items-center justify-center text-[#00ff87] group-hover:scale-105 transition';
            }
        } else if (isImg) {
            if (iconEl) iconEl.textContent = '📸';
            if (badgeEl) {
                badgeEl.textContent = 'OCR IMG';
                badgeEl.className = 'px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/40';
            }
            if (fileSelectedBox) {
                fileSelectedBox.className = 'mt-4 inline-flex items-center gap-2.5 px-3.5 py-2 bg-amber-500/10 dark:bg-amber-950/50 text-amber-600 dark:text-amber-300 border border-amber-500/30 rounded-xl text-xs font-semibold shadow-sm transition-all';
            }
            if (dropIconBox) {
                dropIconBox.className = 'w-14 h-14 rounded-2xl bg-amber-500/10 dark:bg-amber-950/50 border border-amber-500/40 shadow-sm flex items-center justify-center text-amber-400 group-hover:scale-105 transition';
            }
        } else {
            if (iconEl) iconEl.textContent = '📄';
            if (badgeEl) {
                badgeEl.textContent = isDocx ? 'WORD' : (nameLower.endsWith('.pdf') ? 'PDF' : 'DOC');
                badgeEl.className = 'px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/40';
            }
            if (fileSelectedBox) {
                fileSelectedBox.className = 'mt-4 inline-flex items-center gap-2.5 px-3.5 py-2 bg-cyan-500/10 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-300 border border-cyan-500/40 rounded-xl text-xs font-semibold shadow-sm transition-all';
            }
            if (dropIconBox) {
                dropIconBox.className = 'w-14 h-14 rounded-2xl bg-white dark:bg-[#0d1c22] border border-slate-300 dark:border-cyan-700/50 shadow-sm flex items-center justify-center text-emerald-600 dark:text-cyan-400 group-hover:scale-105 group-hover:shadow-neon-sm transition';
            }
        }

        if (isAudio || isVideo) {
            setDetectedContentType('audio', file, isVideo ? 'video' : 'audio');
            openMediaStrategyModal(file, isVideo ? 'video' : 'audio');
        } else {
            setDetectedContentType('text', file);
            const stepVoice = document.getElementById('stepVoiceSection');
            const stepAudio = document.getElementById('stepAudioSection');
            const stepSubmit = document.getElementById('stepSubmitSection');
            if (stepVoice) stepVoice.classList.remove('hidden');
            if (stepAudio) stepAudio.classList.add('hidden');
            if (stepSubmit) stepSubmit.classList.remove('hidden');
            if (submitBtnText) submitBtnText.textContent = '🚀 Comenzar a Crear Audiolibro';
            if (stepVoice) {
                setTimeout(() => stepVoice.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 100);
            }
        }
    }

    // Submit state feedback & overlay activation
    uploadForm.addEventListener('submit', (e) => {
        if (currentInputMode === 'file') {
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                showNoticeModal({
                    title: 'Documento Requerido',
                    message: 'Por favor selecciona o arrastra un archivo antes de comenzar.',
                    type: 'warning'
                });
                return false;
            }

            // If audio file and mode is STT only, trigger direct STT without full page reload
            if (currentDetectedType === 'audio' && currentAudioActionMode === 'stt_only') {
                e.preventDefault();
                triggerDirectSttFromSelectedAudio();
                return false;
            }
        } else {
            const textVal = rawTextInput ? rawTextInput.value.trim() : '';
            if (textVal.length < 10) {
                e.preventDefault();
                showNoticeModal({
                    title: 'Texto Insuficiente',
                    message: 'Por favor ingresa o pega un texto con al menos 10 caracteres para sintetizar el audiolibro.',
                    type: 'warning'
                });
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

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // Interactive Voice Preview Engine
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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
            if (btnIcon) btnIcon.textContent = 'ðŸ”Š';
            if (playerContainer) playerContainer.classList.add('hidden');
            return;
        }

        if (!previewAudio) {
            previewAudio = new Audio();
            previewAudio.addEventListener('ended', () => {
                if (btnText) btnText.textContent = 'Escuchar muestra';
                if (btnIcon) btnIcon.textContent = 'ðŸ”Š';
                if (playerContainer) playerContainer.classList.add('hidden');
            });
            previewAudio.addEventListener('error', (e) => {
                console.warn('Audio playback error:', e);
                if (btnText) btnText.textContent = 'Error al cargar';
                if (btnIcon) btnIcon.textContent = 'âš ï¸';
                setTimeout(() => {
                    if (btnText) btnText.textContent = 'Escuchar muestra';
                    if (btnIcon) btnIcon.textContent = 'ðŸ”Š';
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
        if (btnIcon) btnIcon.textContent = 'â¹ï¸';
        if (statusText) statusText.textContent = `Reproduciendo: ${voiceSelect.options[voiceSelect.selectedIndex].text} (${speedVal}, ${pitchVal})`;
        if (playerContainer) playerContainer.classList.remove('hidden');

        previewAudio.play().catch(e => {
            console.warn('Playback play() was rejected or interrupted:', e);
            if (btnText) btnText.textContent = 'Escuchar muestra';
            if (btnIcon) btnIcon.textContent = 'ðŸ”Š';
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

    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    // Reactive Masonic & Symbolic Content Detector
    // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function checkMasonicPresence() {
        const textVal = rawTextInput ? rawTextInput.value : '';
        const fileVal = fileInput && fileInput.files.length > 0 ? fileInput.files[0].name : '';
        const titleInputEl = document.getElementById('title');
        const titleVal = titleInputEl ? titleInputEl.value : '';
        const combined = (textVal + ' ' + fileVal + ' ' + titleVal).toLowerCase();

        const hasMasonic = 
            /[âˆ´]/.test(combined) ||
            /\.\s*Â·\s*\./.test(combined) ||
            /\.\.\s*\./.test(combined) ||
            /:\./.test(combined) ||
            /\b(gadu|g\.a\.d\.u\.|s\.f\.u\.|t\.a\.f\.|l\.i\.f\.|r\.e\.a\.a\.|i\.p\.h\.|m\.r\.g\.m\.)\b/i.test(combined) ||
            /(?:a\s*\(\s*l\s*\(|ven\s*\(\s*m\s*\(|q\s*\(\s*h\s*\(|qq\s*\(\s*hh\s*\(|resp\s*\(\s*log\s*\(|vvig\s*\(|m\s*\(\s*m\s*\(|s\s*\(\s*f\s*\(\s*u\s*\()/i.test(combined) ||
            /[a-zÃ¡Ã©Ã­Ã³ÃºÃ±]{1,4}\s*[:.Â·]{2,4}\s*[a-zÃ¡Ã©Ã­Ã³ÃºÃ±]{1,4}\s*[:.Â·]{2,4}/i.test(combined) ||
            /\b(venerable\s+maestro|querido\s+hermano|queridos\s+hermanos|gran\s+arquitecto\s+del\s+universo|respetable\s+logia\s+simb[oÃ³]lica|francmasoner[iÃ­]a|salud,\s*fuerza\s*y\s*uni[oÃ³]n|triple\s+abrazo\s+fraternal|abreviatura\s+tripuntuada|la\s+plomada\s*-\s*instrumento)/i.test(combined);

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
