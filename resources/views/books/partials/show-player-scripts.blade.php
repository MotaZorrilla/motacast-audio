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
    let readerFormatMode = localStorage.getItem('motacast_reader_format_mode') || 'markdown'; // 'markdown' | 'raw'
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
        const grpTextFormat = document.getElementById('grpTextFormatControls');
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
            if (grpTextFormat) grpTextFormat.classList.add('hidden');
            if (quickJumpBox) quickJumpBox.classList.remove('hidden');

            if (btnCanvas) {
                btnCanvas.className = "px-2 py-0.5 text-[10px] font-bold rounded-lg bg-white dark:bg-[#071014] text-emerald-600 dark:text-[#00ff87] shadow-sm";
            }
            if (btnText) {
                btnText.className = "px-2 py-0.5 text-[10px] font-bold rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-cyan-200";
            }
            if (drawerTitle) drawerTitle.innerHTML = '<svg class="w-4 h-4 text-[#00ff87]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg><span>Miniaturas de PÃ¡ginas</span>';
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
            if (grpTextFormat) grpTextFormat.classList.remove('hidden');
            if (quickJumpBox) quickJumpBox.classList.add('hidden');

            if (btnCanvas) {
                btnCanvas.className = "px-2 py-0.5 text-[10px] font-bold rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-cyan-200";
            }
            if (btnText) {
                btnText.className = "px-2 py-0.5 text-[10px] font-bold rounded-lg bg-white dark:bg-[#071014] text-emerald-600 dark:text-[#00ff87] shadow-sm";
            }
            if (drawerTitle) drawerTitle.innerHTML = '<svg class="w-4 h-4 text-[#00ff87]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg><span>Ãndice de CapÃ­tulos</span>';
            if (drawerToggleText) drawerToggleText.textContent = 'CapÃ­tulos';

            populateChapterDrawer();
            applyReaderFontSize();
            applyReaderFormatMode();
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
        document.querySelectorAll('.reader-chapter-body, .reader-markdown-view, .reader-raw-view').forEach(el => {
            el.style.fontSize = `${readerFontSize}px`;
        });
    }

    function setReaderFormatMode(mode) {
        readerFormatMode = mode;
        localStorage.setItem('motacast_reader_format_mode', mode);
        applyReaderFormatMode();
    }

    function applyReaderFormatMode() {
        const btnMd = document.getElementById('btnFormatMarkdown');
        const btnRaw = document.getElementById('btnFormatRaw');
        const mdViews = document.querySelectorAll('.reader-markdown-view');
        const rawViews = document.querySelectorAll('.reader-raw-view');

        if (readerFormatMode === 'raw') {
            mdViews.forEach(el => el.classList.add('hidden'));
            rawViews.forEach(el => el.classList.remove('hidden'));
            if (btnRaw) {
                btnRaw.className = "px-2 py-0.5 text-[10px] font-bold rounded-lg bg-white dark:bg-[#071014] text-emerald-600 dark:text-[#00ff87] shadow-sm flex items-center gap-1";
            }
            if (btnMd) {
                btnMd.className = "px-2 py-0.5 text-[10px] font-bold rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-cyan-200 flex items-center gap-1";
            }
        } else {
            mdViews.forEach(el => el.classList.remove('hidden'));
            rawViews.forEach(el => el.classList.add('hidden'));
            if (btnMd) {
                btnMd.className = "px-2 py-0.5 text-[10px] font-bold rounded-lg bg-white dark:bg-[#071014] text-emerald-600 dark:text-[#00ff87] shadow-sm flex items-center gap-1";
            }
            if (btnRaw) {
                btnRaw.className = "px-2 py-0.5 text-[10px] font-bold rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-cyan-200 flex items-center gap-1";
            }
        }
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
                    <span class="text-[10px] font-mono text-slate-400 flex-shrink-0">Leer â†’</span>
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
            console.error('Error al renderizar pÃ¡gina:', err);
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
                        <span class="font-medium truncate">PÃ¡gina ${i}</span>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400">Ver â†’</span>
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
                        loading.innerHTML = '<div class="text-center p-4"><p class="text-rose-500 font-bold text-xs mb-2">No se pudo cargar en visor PDF. Mostrando texto extraÃ­do...</p></div>';
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

        // Apply reader preferences
        applyReaderFontSize();
        applyReaderFormatMode();

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

        const titleText = "Resumen Ejecutivo â€” {{ addslashes($book->title) }}";
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
    // â”€â”€ Phase label map â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    const phaseLabels = {
        pending:      'En cola â€” esperando procesamientoâ€¦',
        extracting:   'Extrayendo texto del documentoâ€¦',
        extracting_ocr: 'Extrayendo texto con OCR (Tesseract)â€¦',
        synthesizing: (done, total) => total
            ? `Sintetizando pista ${done} de ${total} con voz AIâ€¦`
            : 'Sintetizando audio con voz AIâ€¦',
        ready:        'Â¡Listo para escuchar! ðŸŽ§',
        failed:       'Error en el procesamiento.',
    };

    function getPhaseLabel(status, processed, total, ocrUsed) {
        if (status === 'extracting' && ocrUsed) return phaseLabels.extracting_ocr;
        if (status === 'synthesizing') return phaseLabels.synthesizing(processed, total);
        return phaseLabels[status] || 'Procesandoâ€¦';
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
                    if (chTotal)     chTotal.textContent       = data.total_chapters     ?? 'â€”';

                    // OCR badge â€” show if server reported ocr_used (future field) or status says so
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
