{{-- Executive Summary Card (NLP Synthesized) --}}
@if ($book->summary)
    <div class="card-tactile rounded-2xl p-4 sm:p-5 relative overflow-hidden border border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-[#090d16]/95">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/80 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Resumen Ejecutivo</span>
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">Sinopsis inteligente</span>
            </div>

            @if ($book->summary_audio_path)
                <button 
                    type="button" 
                    onclick="playSummary()" 
                    id="btnPlaySummary"
                    class="btn-primary-tactile px-3.5 py-1.5 rounded-xl text-xs font-black inline-flex items-center justify-center gap-2 shadow-sm text-white self-start sm:self-auto hover:scale-105 active:scale-95 transition"
                    title="Escuchar audio del resumen"
                >
                    <svg class="w-3.5 h-3.5 text-white icon-play" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                    </svg>
                    <svg class="w-3.5 h-3.5 text-white icon-pause hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>🎧 Escuchar Resumen</span>
                </button>
            @endif
        </div>

        <!-- Summary Body with Collapse / Expand -->
        <div class="summary-container pt-3" data-expanded="false">
            <p class="summary-text text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed line-clamp-3 transition-all">
                {{ $book->summary }}
            </p>
            @if (mb_strlen($book->summary) > 160)
                <button type="button" onclick="toggleSummary(this)" class="mt-2 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1 transition">
                    <span class="btn-label">Ver más</span>
                    <svg class="w-3.5 h-3.5 chevron transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            @endif
        </div>
    </div>
@endif
