@php
    $fileExt = strtolower(pathinfo($book->original_filename ?? $book->pdf_path, PATHINFO_EXTENSION)) ?: 'pdf';
    $isPdf = ($fileExt === 'pdf');
@endphp

<style>
    /* Real-Time Read-Along Karaoke Highlighting & Typography */
    .read-along-paragraph {
        position: relative;
        transition: background-color 0.25s ease, border-left-color 0.25s ease, box-shadow 0.25s ease, padding 0.25s ease;
        border-radius: 0.75rem;
        padding: 0.5rem 0.75rem;
        margin-left: -0.75rem;
        margin-right: -0.75rem;
        margin-bottom: 0.75rem;
        cursor: pointer;
        border-left: 3px solid transparent;
    }
    .read-along-paragraph:hover {
        background-color: rgba(99, 102, 241, 0.08);
    }
    .read-along-active {
        background-color: rgba(99, 102, 241, 0.14) !important;
        border-left: 4px solid #6366f1 !important;
        padding-left: calc(0.75rem - 1px) !important;
        box-shadow: 0 0 20px rgba(99, 102, 241, 0.22), inset 0 0 10px rgba(99, 102, 241, 0.05);
        border-top-left-radius: 0.25rem;
        border-bottom-left-radius: 0.25rem;
    }
    .dark .read-along-active {
        background-color: rgba(99, 102, 241, 0.2) !important;
        border-left: 4px solid #818cf8 !important;
        color: #f8fafc !important;
        box-shadow: 0 0 25px rgba(99, 102, 241, 0.35), inset 0 0 15px rgba(99, 102, 241, 0.1);
    }
    .active-reading-chapter {
        border-color: rgba(99, 102, 241, 0.6) !important;
        box-shadow: 0 0 25px rgba(99, 102, 241, 0.15);
    }
</style>

<!-- Integrated Universal In-App Document Reader Modal (PDF Canvas + Markdown / DOCX / TXT Typography Reader) -->
<div id="pdfViewerModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-md p-1 sm:p-3 md:p-5 flex flex-col items-center justify-center">
    <div class="card-tactile rounded-2xl w-full max-w-6xl h-[98vh] sm:h-[95vh] flex flex-col overflow-hidden shadow-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#090d16]">
        
        <!-- Modal Top Bar: Title, Mode Switcher, Thumbnails/Chapters Toggle, Zoom/Text Controls & Window Actions -->
        <div class="px-2.5 sm:px-4 py-2 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-white/95 dark:bg-[#090d16]/95 flex-shrink-0 gap-1.5 sm:gap-2">
            <!-- Book Info, Format Badge & Drawer Toggle -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 min-w-0">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center flex-shrink-0 font-bold text-xs shadow-sm shadow-indigo-500/30">
                    📖
                </div>
                <div class="min-w-0 max-w-[90px] xs:max-w-[130px] sm:max-w-[200px] md:max-w-[260px]">
                    <div class="flex items-center gap-1.5">
                        <p class="text-xs sm:text-sm font-black text-slate-900 dark:text-white truncate leading-tight">{{ $book->title }}</p>
                        <span class="uppercase px-1.5 py-0.2 rounded text-[9px] font-mono font-black {{ $isPdf ? 'bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 border border-rose-300 dark:border-rose-800/40' : ($fileExt === 'docx' ? 'bg-blue-100 dark:bg-blue-950/70 text-blue-600 dark:text-blue-400 border border-blue-300 dark:border-blue-800/40' : ($fileExt === 'md' || $fileExt === 'markdown' ? 'bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400 border border-purple-300 dark:border-purple-800/40' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700')) }}">
                            {{ $fileExt }}
                        </span>
                    </div>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate hidden xs:block" id="readerSubTitle">Visor Universal In-App</p>
                </div>

                <!-- Navigation Drawer Toggle Button (Miniaturas for PDF, Capítulos for Non-PDF/Text) -->
                <button 
                    type="button" 
                    id="btnToggleThumbnails"
                    onclick="toggleThumbnailsDrawer()" 
                    class="px-2 py-1 rounded-xl text-[10px] sm:text-[11px] font-bold bg-slate-100 dark:bg-slate-900 hover:bg-indigo-500/10 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 flex items-center gap-1 transition flex-shrink-0"
                    title="Alternar panel de navegación"
                >
                    <svg class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span id="lblDrawerToggleText" class="hidden sm:inline">{{ $isPdf ? 'Miniaturas' : 'Capítulos' }}</span>
                    <span id="badgeThumbnailsCount" class="font-mono text-[10px] text-slate-400 dark:text-slate-500"></span>
                </button>

                <!-- PDF vs Text View Switcher (Only shown if document is PDF) -->
                @if ($isPdf)
                <div class="hidden sm:flex items-center p-0.5 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <button type="button" id="btnModeCanvas" onclick="setReaderViewMode('canvas')" class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm">
                        PDF
                    </button>
                    <button type="button" id="btnModeText" onclick="setReaderViewMode('text')" class="px-2 py-0.5 text-[10px] font-bold rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white">
                        Texto
                    </button>
                </div>
                @endif
            </div>

            <!-- Right Controls: Zoom (for PDF), Font Size (for Text), External Link, Close -->
            <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
                <!-- Zoom Controls for PDF Canvas Mode -->
                <div id="grpPdfZoomControls" class="{{ $isPdf ? 'flex' : 'hidden' }} items-center gap-0.5 sm:gap-1 bg-slate-100 dark:bg-slate-900 p-0.5 rounded-xl border border-slate-200 dark:border-slate-800">
                    <button type="button" onclick="zoomPdf(-0.15)" class="w-6 h-6 flex items-center justify-center text-xs text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold" title="Reducir Zoom (-)">
                        -
                    </button>
                    <button type="button" onclick="resetZoomPdf()" id="lblZoomLevel" class="hidden sm:block px-2 py-0.5 text-[10px] text-slate-600 dark:text-slate-300 font-mono font-bold" title="Ajustar Automático">
                        Ajustar
                    </button>
                    <button type="button" onclick="zoomPdf(0.15)" class="w-6 h-6 flex items-center justify-center text-xs text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold" title="Aumentar Zoom (+)">
                        +
                    </button>
                </div>

                <!-- Font Size Controls for Universal Text Mode -->
                <div id="grpTextFontControls" class="{{ $isPdf ? 'hidden' : 'flex' }} items-center gap-0.5 sm:gap-1 bg-slate-100 dark:bg-slate-900 p-0.5 rounded-xl border border-slate-200 dark:border-slate-800">
                    <button type="button" onclick="adjustReaderFontSize(-1)" class="w-6 h-6 flex items-center justify-center text-xs text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold" title="Disminuir tamaño de letra">
                        A-
                    </button>
                    <span id="lblReaderFontSize" class="hidden sm:block px-1.5 py-0.5 text-[10px] text-slate-600 dark:text-slate-300 font-mono font-bold">15px</span>
                    <button type="button" onclick="adjustReaderFontSize(1)" class="w-6 h-6 flex items-center justify-center text-xs text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold" title="Aumentar tamaño de letra">
                        A+
                    </button>
                </div>

                <!-- Markdown vs Raw Text Format Toggle -->
                <div id="grpTextFormatControls" class="{{ $isPdf ? 'hidden' : 'flex' }} items-center p-0.5 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <button type="button" id="btnFormatMarkdown" onclick="setReaderFormatMode('markdown')" class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm flex items-center gap-1" title="Visualización formateada con Markdown">
                        <span>🎨 Markdown</span>
                    </button>
                    <button type="button" id="btnFormatRaw" onclick="setReaderFormatMode('raw')" class="px-2 py-0.5 text-[10px] font-bold rounded-lg text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white flex items-center gap-1" title="Visualización de texto plano original">
                        <span>📝 Original</span>
                    </button>
                </div>

                <!-- Auto-Scroll Read-Along Follow Toggle -->
                <button 
                    type="button" 
                    id="btnToggleAutoScroll" 
                    onclick="toggleAutoScroll()" 
                    class="px-2 py-1 rounded-xl text-[10px] sm:text-[11px] font-bold bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30 flex items-center gap-1 transition flex-shrink-0 shadow-sm"
                    title="Alternar seguimiento y auto-scroll de lectura en tiempo real"
                >
                    <span id="iconAutoScroll">🎯</span>
                    <span id="lblAutoScrollText" class="hidden sm:inline">Auto-scroll</span>
                </button>

                <!-- Download Transcription (TXT / Markdown / Copy) -->
                <div class="relative inline-block text-left">
                    <button 
                        type="button" 
                        onclick="toggleTranscriptionMenu()" 
                        class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-800 transition shadow-sm"
                        title="Descargar o copiar transcripción de texto"
                    >
                        <span>📄 Texto</span>
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="menuTranscription" class="hidden absolute right-0 mt-1 w-48 rounded-xl bg-white dark:bg-[#090d16] border border-slate-200 dark:border-slate-800 shadow-2xl py-1 z-50 text-xs font-medium">
                        <button type="button" onclick="copyReaderFullText()" class="w-full text-left px-3 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-900 flex items-center gap-2">
                            <span>📋 Copiar al Portapapeles</span>
                        </button>
                        <a href="{{ route('books.transcription.download', ['book' => $book->id, 'format' => 'txt']) }}" download class="block px-3 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-900 flex items-center gap-2">
                            <span>📥 Descargar .TXT</span>
                        </a>
                        <a href="{{ route('books.transcription.download', ['book' => $book->id, 'format' => 'md']) }}" download class="block px-3 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-900 flex items-center gap-2">
                            <span>📥 Descargar .MD (Markdown)</span>
                        </a>
                    </div>
                </div>

                <!-- Open in external tab / download -->
                <a href="{{ route('books.pdf', $book->id) }}" target="_blank" class="hidden md:inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-800 transition" title="Abrir / descargar documento original">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span>Original</span>
                </a>

                <!-- Close Modal Button -->
                <button type="button" onclick="closePdfModal()" class="p-1 sm:p-1.5 text-slate-400 hover:text-rose-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition" title="Cerrar Lector (Esc)">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Middle Content Area: Drawer + Dynamic Viewer (Canvas vs Text) -->
        <div class="flex-grow w-full flex overflow-hidden relative">
            
            <!-- Side Navigation Drawer (Thumbnails for PDF, Chapter Index for Text) -->
            <div id="pdfThumbnailsDrawer" class="hidden w-64 sm:w-72 bg-slate-50/95 dark:bg-[#090d16]/95 border-r border-slate-200 dark:border-slate-800 flex-col flex-shrink-0 z-30 transition-all duration-300 h-full">
                <!-- Drawer Header -->
                <div class="p-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <span id="drawerTitleText" class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        <span>{{ $isPdf ? 'Miniaturas de Páginas' : 'Índice de Capítulos' }}</span>
                    </span>
                    <button type="button" onclick="toggleThumbnailsDrawer()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs font-bold p-1">
                        &times; Cerrar
                    </button>
                </div>
                
                <!-- Quick Jump Bar (For PDF pages) -->
                <div id="drawerQuickJumpBox" class="{{ $isPdf ? 'flex' : 'hidden' }} p-2 border-b border-slate-200 dark:border-slate-800 items-center gap-2">
                    <input 
                        type="number" 
                        id="quickPageJumpInput" 
                        min="1" 
                        placeholder="N° pág" 
                        class="w-full px-2.5 py-1 text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-white focus:ring-1 focus:ring-indigo-500 focus:outline-none font-mono"
                    >
                    <button 
                        type="button" 
                        onclick="handleQuickJump()" 
                        class="px-2.5 py-1 btn-primary-tactile text-white font-bold rounded-lg text-xs transition"
                    >
                        Ir
                    </button>
                </div>

                <!-- Scrollable Navigation Items Container -->
                <div id="pdfThumbnailsContainer" class="flex-grow overflow-y-auto p-2 space-y-1.5">
                    <!-- Dynamic Page or Chapter chips populated by JS -->
                </div>
            </div>

            <!-- VIEW 1: PDF Canvas Scrollable Area -->
            <div id="pdfCanvasWrapper" class="{{ $isPdf ? 'flex' : 'hidden' }} flex-grow w-full bg-slate-100/70 dark:bg-[#070a12] p-2 sm:p-4 overflow-y-auto relative flex-col items-center">
                <!-- Loading Spinner Indicator -->
                <div id="pdfLoadingIndicator" class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 dark:bg-[#090d16]/80 z-20">
                    <div class="w-10 h-10 border-4 border-indigo-500/20 border-t-indigo-500 rounded-full animate-spin mb-3"></div>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Cargando documento...</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Renderizando en alta definición HiDPI Retina</p>
                </div>

                <!-- Native HTML5 Canvas Element -->
                <canvas id="pdfCanvas" class="shadow-2xl rounded-xl bg-white max-w-full my-auto transition-transform duration-150"></canvas>

                <!-- Floating PDF Live Read-Along Sync HUD -->
                <div id="pdfSyncHud" class="sticky bottom-2 z-30 max-w-xl w-11/12 mx-auto card-tactile rounded-2xl p-2 sm:p-2.5 bg-white/95 dark:bg-[#090d16]/95 border border-indigo-500/30 shadow-2xl backdrop-blur-md flex items-center justify-between gap-2.5 transition-all duration-300">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse flex-shrink-0 shadow-sm shadow-indigo-500/50"></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[9px] font-mono font-black uppercase text-indigo-600 dark:text-indigo-400">Sincronía en Vivo</span>
                                <span id="pdfSyncPageBadge" class="text-[9px] font-mono px-1 rounded bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-300">Pág. 1</span>
                            </div>
                            <p id="pdfSyncSnippetText" class="text-xs text-slate-800 dark:text-slate-200 font-medium truncate mt-0.5">Sincronizando audio con documento...</p>
                        </div>
                    </div>
                    <button type="button" onclick="setReaderViewMode('text')" class="px-2.5 py-1 text-[10px] sm:text-[11px] font-black rounded-lg btn-primary-tactile text-white flex items-center gap-1 flex-shrink-0 shadow-sm" title="Ver texto guiado con resaltado de párrafos">
                        <span>📖 Ver Texto Guiado</span>
                    </button>
                </div>
            </div>

            <!-- VIEW 2: Universal Editorial Text Reader (DOCX, Markdown, TXT & PDF Extracted Text) -->
            <div id="documentTextWrapper" class="{{ $isPdf ? 'hidden' : 'block' }} flex-grow w-full bg-slate-50 dark:bg-[#090d16] p-3 sm:p-6 md:p-8 overflow-y-auto">
                <div class="max-w-3xl mx-auto space-y-6" id="readerTextContentContainer">
                    
                    <!-- Executive Summary Callout if available -->
                    @if ($book->summary)
                    <div class="p-4 sm:p-5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200/60 dark:border-indigo-800/30 shadow-sm space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-indigo-800 dark:text-indigo-300 flex items-center gap-1.5 uppercase tracking-wider">
                                <span>🧠</span> Resumen Ejecutivo del Documento
                            </span>
                            @if ($book->summary_audio_path)
                            <button type="button" onclick="playSummary()" class="px-2.5 py-1 text-[11px] font-bold rounded-lg btn-primary-tactile text-white shadow-sm flex items-center gap-1">
                                <span>🎧 Escuchar Resumen</span>
                            </button>
                            @endif
                        </div>
                        <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed font-sans">
                            {{ $book->summary }}
                        </p>
                    </div>
                    @endif

                    <!-- Chapters Text Rendered Dynamically or from Blade -->
                    <div id="readerChaptersList" class="space-y-6">
                        @forelse ($book->chapters as $ch)
                        <article id="readerChapSection-{{ $ch->chapter_number }}" data-chapter-number="{{ $ch->chapter_number }}" data-chapter-id="{{ $ch->id }}" class="card-tactile rounded-2xl p-5 sm:p-6 bg-white dark:bg-[#0b101b] border border-slate-200 dark:border-slate-800/80 shadow-sm transition hover:border-indigo-500/40">
                            <!-- Chapter Header Bar -->
                            <div class="flex flex-wrap items-center justify-between gap-2 pb-3 mb-4 border-b border-slate-100 dark:border-slate-800">
                                <div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                        Pista #{{ $ch->chapter_number }}
                                    </span>
                                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">
                                        {{ $ch->title }}
                                    </h2>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($ch->duration_seconds > 0)
                                    <span class="text-xs font-mono text-slate-500 dark:text-slate-400">⏱️ {{ $ch->formatted_duration }}</span>
                                    @endif
                                    @if ($ch->status === 'ready')
                                    <button 
                                        type="button" 
                                        onclick="selectChapter({{ $ch->id }}, true)"
                                        class="px-2.5 py-1 text-[11px] font-black rounded-lg btn-primary-tactile text-white shadow-sm flex items-center gap-1"
                                        title="Reproducir audio de este capítulo"
                                    >
                                        <span>▶ Escuchar</span>
                                    </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Chapter Body: Markdown Formatted View & Raw Original View -->
                            <div class="reader-chapter-body" style="font-size: var(--reader-font-size, 15px);">
                                <!-- Formatted Markdown View -->
                                <div class="reader-markdown-view prose prose-slate dark:prose-invert max-w-none text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed font-sans select-text">
                                    {!! \Illuminate\Support\Str::markdown($ch->content_text ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                                </div>

                                <!-- Raw Original Plain Text View -->
                                <div class="reader-raw-view hidden prose prose-slate dark:prose-invert max-w-none text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed font-mono whitespace-pre-wrap select-text">
                                    {{ $ch->content_text ?? '' }}
                                </div>
                            </div>
                        </article>
                        @empty
                        <div class="text-center py-12 text-slate-400">
                            <p class="text-sm font-bold">No hay capítulos de texto disponibles aún.</p>
                            <p class="text-xs mt-1">El documento podría estar en fase de procesamiento o no contener texto legible.</p>
                        </div>
                        @endforelse
                    </div>

                </div>
            </div>

        </div>

        <!-- Bottom Ergonomic Bar (Switches depending on Canvas vs Text view) -->
        <!-- Sub-bar A: PDF Page Navigation Bar -->
        <div id="pdfPaginationBar" class="{{ $isPdf ? 'flex' : 'hidden' }} px-2 sm:px-4 py-1.5 sm:py-2 bg-slate-100/95 dark:bg-[#090d16]/95 border-t border-slate-200 dark:border-slate-800 items-center justify-between flex-shrink-0 gap-1 sm:gap-2">
            <!-- Left: First Page & Prev Page -->
            <div class="flex items-center gap-1">
                <button 
                    type="button" 
                    onclick="goToPdfPage(1)" 
                    class="px-2 py-1 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 hover:bg-indigo-500/10 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 transition disabled:opacity-30 disabled:pointer-events-none"
                    id="btnPdfFirst"
                    title="Primera Página"
                >
                    <span>⏮</span><span class="hidden sm:inline ml-1">Inicio</span>
                </button>
                <button 
                    type="button" 
                    id="btnPdfPrev" 
                    onclick="onPdfPrevPage()" 
                    class="px-2.5 sm:px-3 py-1 sm:py-1.5 text-xs font-black rounded-xl btn-primary-tactile text-white flex items-center gap-1 shadow-sm disabled:opacity-30 disabled:pointer-events-none transition"
                    title="Página Anterior (←)"
                >
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden sm:inline">Anterior</span>
                </button>
            </div>

            <!-- Center: Interactive Page Indicator / Input -->
            <div class="flex items-center gap-1 text-xs font-bold text-slate-800 dark:text-slate-200">
                <span class="text-slate-500 dark:text-slate-400 hidden xs:inline text-[11px]">Pág</span>
                <input 
                    type="number" 
                    id="pdfCurrentPageInput" 
                    min="1" 
                    value="1" 
                    onchange="handlePageInputChange(this.value)"
                    class="w-10 sm:w-12 px-1 py-0.5 text-center font-mono font-bold text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-lg text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                >
                <span class="text-slate-500 dark:text-slate-400 text-xs">/ <span id="pdfTotalPages" class="font-mono text-slate-700 dark:text-slate-300">--</span></span>
            </div>

            <!-- Right: Next Page & Last Page -->
            <div class="flex items-center gap-1">
                <button 
                    type="button" 
                    id="btnPdfNext" 
                    onclick="onPdfNextPage()" 
                    class="px-2.5 sm:px-3 py-1 sm:py-1.5 text-xs font-black rounded-xl btn-primary-tactile text-white flex items-center gap-1 shadow-sm disabled:opacity-30 disabled:pointer-events-none transition"
                    title="Página Siguiente (→)"
                >
                    <span class="hidden sm:inline">Siguiente</span>
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <button 
                    type="button" 
                    onclick="goToLastPdfPage()" 
                    class="px-2 py-1 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 hover:bg-indigo-500/10 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 transition disabled:opacity-30 disabled:pointer-events-none"
                    id="btnPdfLast"
                    title="Última Página"
                >
                    <span class="hidden sm:inline mr-1">Fin</span><span>⏭</span>
                </button>
            </div>
        </div>

        <!-- Sub-bar B: Universal Text Chapter Navigation Bar -->
        <div id="textPaginationBar" class="{{ $isPdf ? 'hidden' : 'flex' }} px-2 sm:px-4 py-1.5 sm:py-2 bg-slate-100/95 dark:bg-[#090d16]/95 border-t border-slate-200 dark:border-slate-800 items-center justify-between flex-shrink-0 gap-1 sm:gap-2">
            <div class="flex items-center gap-1">
                <button 
                    type="button" 
                    onclick="jumpToReaderAdjacentChapter(-1)" 
                    class="px-2.5 sm:px-3 py-1 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 hover:bg-indigo-500/10 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 transition flex items-center gap-1"
                    title="Capítulo Anterior"
                >
                    <span>◀</span><span class="hidden sm:inline">Capítulo Anterior</span>
                </button>
            </div>

            <div class="text-xs font-mono font-bold text-slate-600 dark:text-slate-300">
                <span>Capítulos: <strong id="lblActiveReaderChapNum" class="text-indigo-600 dark:text-indigo-400">1</strong> / {{ $book->chapters->count() }}</span>
            </div>

            <div class="flex items-center gap-1">
                <button 
                    type="button" 
                    onclick="jumpToReaderAdjacentChapter(1)" 
                    class="px-2.5 sm:px-3 py-1 text-xs font-bold rounded-xl bg-white dark:bg-slate-900 hover:bg-indigo-500/10 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 transition flex items-center gap-1"
                    title="Capítulo Siguiente"
                >
                    <span class="hidden sm:inline">Capítulo Siguiente</span><span>▶</span>
                </button>
            </div>
        </div>

        <!-- Modal Full Integrated Audio Playback Console -->
        <div id="modalAudioConsole" class="px-3 sm:px-6 py-2.5 bg-white/95 dark:bg-[#090d16]/95 border-t border-slate-200 dark:border-slate-800 shadow-[0_-8px_25px_rgba(0,0,0,0.08)] flex-shrink-0">
            <div class="flex flex-col md:flex-row items-center justify-between gap-2.5 sm:gap-4">
                
                <!-- Track Info inside Modal -->
                <div class="flex items-center gap-2.5 w-full md:w-1/4 min-w-0">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center flex-shrink-0 shadow-sm shadow-indigo-500/20">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate" id="modalChapterTitle">Selecciona una pista</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">{{ $book->title }}</p>
                    </div>
                </div>

                <!-- Center Controls: Prev, Rewind 15s, Play/Pause, Forward 15s, Next + Scrubber -->
                <div class="flex flex-col items-center gap-1 w-full md:w-2/4">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <!-- Previous Chapter -->
                        <button type="button" id="btnModalPrevChapter" class="p-1 text-slate-400 dark:text-slate-500 hover:text-slate-900 dark:hover:text-white transition" title="Pista Anterior">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                            </svg>
                        </button>

                        <!-- Rewind 15s -->
                        <button type="button" id="btnModalRewind15" class="p-1 text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition" title="Retroceder 15s">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z" />
                            </svg>
                        </button>

                        <!-- Master Play/Pause inside Modal -->
                        <button type="button" id="btnModalMasterPlay" onclick="togglePlay()" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full btn-primary-tactile text-white flex items-center justify-center shadow-indigo-500/25 transition transform hover:scale-105 active:scale-95" title="Reproducir / Pausar">
                            <svg id="iconModalPlay" class="w-4 h-4 sm:w-5 sm:h-5 ml-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                            <svg id="iconModalPause" class="w-4 h-4 sm:w-5 sm:h-5 hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Forward 15s -->
                        <button type="button" id="btnModalForward15" class="p-1 text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition" title="Adelantar 15s">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.933 12.8a1 1 0 000-1.6L6.6 7.2A1 1 0 005 8v8a1 1 0 001.6.8l5.333-4zM19.933 12.8a1 1 0 000-1.6l-5.333-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.333-4z" />
                            </svg>
                        </button>

                        <!-- Next Chapter -->
                        <button type="button" id="btnModalNextChapter" class="p-1 text-slate-400 dark:text-slate-500 hover:text-slate-900 dark:hover:text-white transition" title="Siguiente Pista">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Scrubber Timeline & Elapsed Time inside Modal -->
                    <div class="w-full flex items-center gap-2">
                        <span id="modalCurrentTime" class="font-mono text-[10px] text-slate-500 dark:text-slate-400 w-8 text-right font-semibold">00:00</span>
                        <input 
                            type="range" 
                            id="modalScrubber" 
                            min="0" 
                            max="100" 
                            value="0" 
                            class="w-full h-1 bg-slate-200 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-indigo-500 focus:outline-none"
                        >
                        <span id="modalDuration" class="font-mono text-[10px] text-slate-500 dark:text-slate-400 w-8 font-semibold">00:00</span>
                    </div>
                </div>

                <!-- Right Side: Speed Selector, Volume, Download inside Modal -->
                <div class="flex items-center justify-end gap-2 w-full md:w-1/4">
                    <!-- Speed Switcher -->
                    <select id="modalPlaybackRate" class="px-2 py-0.5 text-xs font-bold bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl border border-slate-300 dark:border-slate-800 focus:ring-1 focus:ring-indigo-500 transition cursor-pointer">
                        <option value="0.75">0.75x</option>
                        <option value="1.0" selected>1.0x</option>
                        <option value="1.25">1.25x</option>
                        <option value="1.5">1.5x</option>
                        <option value="2.0">2.0x</option>
                    </select>

                    <!-- Volume Mute Toggle -->
                    <button type="button" id="btnModalMuteToggle" class="p-1 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition" title="Silenciar">
                        <svg id="iconModalVolumeHigh" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        </svg>
                        <svg id="iconModalVolumeMuted" class="w-4 h-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                        </svg>
                    </button>

                    <!-- Download Current Track -->
                    <a id="btnModalDownload" href="#" class="p-1 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition" title="Descargar MP3 de la pista actual">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>
