{{-- Status & Progress Banners (Universal Responsive) --}}
<div id="processingBanner" class="{{ $book->isProcessing() ? 'block' : 'hidden' }} card-tactile rounded-2xl overflow-hidden border border-indigo-500/30 dark:border-indigo-500/30 bg-indigo-50/90 dark:bg-[#090d16] p-0.5 shadow-sm">
    <div class="flex items-center justify-between px-4 py-3 text-xs font-bold text-indigo-950 dark:text-indigo-200">
        <span class="flex items-center gap-2.5">
            <span class="relative flex-shrink-0 w-5 h-5">
                <span class="absolute inset-0 rounded-full border-2 border-indigo-500/20 dark:border-indigo-800/30"></span>
                <svg class="w-5 h-5 animate-spin text-indigo-600 dark:text-indigo-400 drop-shadow-[0_0_6px_rgba(99,102,241,0.4)]" fill="none" viewBox="0 0 24 24">
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
             class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-2 transition-all duration-500 shadow-[0_0_8px_rgba(99,102,241,0.5)]"
             style="width: {{ $book->progress_percentage }}%">
        </div>
    </div>

    <div class="px-4 py-2.5 flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400 font-mono border-t border-indigo-200/50 dark:border-slate-800">
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
                <button type="submit" class="px-4 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    Reintentar
                </button>
            </form>
        </div>
    </div>
@endif
