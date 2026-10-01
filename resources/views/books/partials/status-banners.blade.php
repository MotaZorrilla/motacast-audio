{{-- Status & Progress Banners (Universal Responsive) --}}
<div id="processingBanner" class="{{ $book->isProcessing() ? 'block' : 'hidden' }} card-tactile rounded-2xl overflow-hidden border border-emerald-500/30 dark:border-cyan-700/40 bg-emerald-50/90 dark:bg-[#071014] p-0.5 shadow-neon-sm">
    <div class="flex items-center justify-between px-4 py-3 text-xs font-bold text-emerald-900 dark:text-[#00ff87]">
        <span class="flex items-center gap-2.5">
            <span class="relative flex-shrink-0 w-5 h-5">
                <span class="absolute inset-0 rounded-full border-2 border-[#00ff87]/20 dark:border-cyan-800/30"></span>
                <svg class="w-5 h-5 animate-spin text-[#00ff87] drop-shadow-[0_0_6px_#00ff87]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </span>
            <span id="processingDetail" class="leading-tight">Iniciando procesamiento…</span>
        </span>
        <span id="processingPercentage" class="font-mono text-xs tabular-nums ml-2 shrink-0">{{ $book->progress_percentage }}%</span>
    </div>

    <div class="w-full bg-slate-200 dark:bg-slate-800/80 h-2">
        <div id="processingProgressBar"
             class="bg-[#00ff87] h-2 transition-all duration-500 shadow-[0_0_8px_#00ff87]"
             style="width: {{ $book->progress_percentage }}%">
        </div>
    </div>

    <div class="px-4 py-2.5 flex items-center justify-between text-[10px] text-slate-500 dark:text-sky-400/70 font-mono border-t border-emerald-200/50 dark:border-cyan-900/30">
        <span id="processingChaptersDetail">Capítulos: <span id="procChDone">{{ $book->processed_chapters }}</span> / <span id="procChTotal">{{ $book->total_chapters ?? '—' }}</span></span>
        <span id="processingOcrBadge" class="hidden items-center gap-1 px-2 py-0.5 rounded-lg bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border border-amber-300/40 dark:border-amber-700/30 text-[10px] font-bold">
            🔍 OCR activo
        </span>
    </div>
</div>

@if ($book->hasFailed())
    <div class="card-tactile rounded-2xl p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-500/30 text-rose-900 dark:text-rose-200">
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <p class="text-sm font-bold text-rose-800 dark:text-rose-300">Hubo un inconveniente al procesar el archivo</p>
                <p class="text-xs text-rose-700 dark:text-rose-400 font-mono">{{ $book->error_message }}</p>
            </div>
            <form action="{{ route('books.retry', $book->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    Reintentar
                </button>
            </form>
        </div>
    </div>
@endif
