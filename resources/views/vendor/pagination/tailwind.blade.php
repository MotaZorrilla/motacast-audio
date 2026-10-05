@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between py-3">
        {{-- Mobile Navigation (Simple Previous / Next) --}}
        <div class="flex justify-between flex-1 sm:hidden gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 dark:bg-[#071014] text-slate-400 dark:text-cyan-900 border border-slate-300 dark:border-cyan-950/60 cursor-not-allowed opacity-50">
                    &laquo; Anterior
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center px-4 py-2 text-xs font-bold rounded-xl bg-white dark:bg-[#071014] text-slate-700 dark:text-cyan-300 border border-slate-300 dark:border-cyan-800/60 hover:border-[#00ff87] hover:text-emerald-600 dark:hover:text-[#00ff87] transition shadow-sm">
                    &laquo; Anterior
                </a>
            @endif

            <span class="inline-flex items-center px-3 py-2 text-xs font-mono font-bold text-slate-500 dark:text-sky-300">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center px-4 py-2 text-xs font-bold rounded-xl bg-white dark:bg-[#071014] text-slate-700 dark:text-cyan-300 border border-slate-300 dark:border-cyan-800/60 hover:border-[#00ff87] hover:text-emerald-600 dark:hover:text-[#00ff87] transition shadow-sm">
                    Siguiente &raquo;
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 dark:bg-[#071014] text-slate-400 dark:text-cyan-900 border border-slate-300 dark:border-cyan-950/60 cursor-not-allowed opacity-50">
                    Siguiente &raquo;
                </span>
            @endif
        </div>

        {{-- Desktop Navigation (Full Pagination with Results Counter) --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs text-slate-600 dark:text-sky-300/80 font-mono">
                    Mostrando
                    @if ($paginator->firstItem())
                        <span class="font-bold text-slate-900 dark:text-cyan-200">{{ $paginator->firstItem() }}</span>
                        a
                        <span class="font-bold text-slate-900 dark:text-cyan-200">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    de
                    <span class="font-bold text-slate-900 dark:text-[#00ff87]">{{ $paginator->total() }}</span>
                    documentos
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex items-center shadow-sm rounded-xl p-1 bg-slate-100 dark:bg-[#071014] border border-slate-200 dark:border-cyan-900/60 gap-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="relative inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-400 dark:text-cyan-900 cursor-not-allowed opacity-50">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}" class="relative inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 dark:text-cyan-300 hover:text-emerald-600 dark:hover:text-[#00ff87] hover:bg-slate-200 dark:hover:bg-[#00ff87]/10 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true" class="relative inline-flex items-center px-3 py-1.5 text-xs font-mono text-slate-400 dark:text-cyan-700">
                                {{ $element }}
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="relative inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-mono font-bold bg-[#00ff87]/20 border border-[#00ff87] text-[#00c965] dark:text-[#00ff87] shadow-neon-sm">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-mono font-semibold text-slate-600 dark:text-cyan-300 hover:text-emerald-600 dark:hover:text-[#00ff87] hover:bg-slate-200 dark:hover:bg-[#00ff87]/10 transition">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}" class="relative inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 dark:text-cyan-300 hover:text-emerald-600 dark:hover:text-[#00ff87] hover:bg-slate-200 dark:hover:bg-[#00ff87]/10 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="relative inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-400 dark:text-cyan-900 cursor-not-allowed opacity-50">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
