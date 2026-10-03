@extends('layouts.app')

@section('title', 'Telemetría & Métricas - Admin MotaCastAudio')

@section('content')
<div class="space-y-6">

    <!-- Admin Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-cyan-950/60 pb-3 overflow-x-auto">
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-600 dark:text-sky-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Usuarios & Cuotas</span>
        </a>
        <a href="{{ route('admin.telemetry.index') }}" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-teal-700 dark:text-cyan-300 bg-teal-500/10 dark:bg-cyan-950/50 border border-teal-500/30 dark:border-cyan-500/40 transition inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-[#00c965] dark:text-[#00ff87]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Telemetría & Métricas</span>
        </a>
        <a href="{{ route('admin.tickets.index') }}" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-600 dark:text-sky-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span>Tickets & Soporte</span>
            @if ($ticketMetrics['pending_tickets'] > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-500 text-slate-950">
                    {{ $ticketMetrics['pending_tickets'] }}
                </span>
            @endif
        </a>
    </div>

    <!-- Header & Live Status Banner -->
    <div class="card-tactile rounded-2xl p-5 sm:p-6 relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-[#00ff87]/10 dark:bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-emerald-500/15 text-emerald-600 dark:text-[#00ff87] border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00ff87] animate-pulse"></span> Telemetría del Sistema
                    </span>
                    <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">&bull; Homelab & Síntesis</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-cyan-200 tracking-tight">Observabilidad en Tiempo Real</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-sky-300 mt-0.5">
                    Monitorea almacenamiento de audios, cuotas de usuarios, desempeño del motor Piper TTS y registros del servidor.
                </p>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-[#071014] border border-slate-200 dark:border-cyan-900/40 text-xs font-mono text-slate-600 dark:text-sky-300 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Tasa Éxito: <strong>{{ $conversionMetrics['success_rate'] }}%</strong></span>
                </span>
            </div>
        </div>

        <!-- 4 Essential Telemetry Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5 pt-5 border-t border-slate-200/80 dark:border-cyan-950/60 relative z-10">
            <div class="bg-slate-50 dark:bg-[#071014] rounded-xl p-3.5 border border-slate-200/80 dark:border-cyan-900/30 shadow-inner">
                <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase tracking-wider">Espacio en Audios</p>
                <p class="text-2xl font-black text-slate-900 dark:text-cyan-300 mt-0.5">{{ $storageMetrics['audiobooks_size'] }}</p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $storageMetrics['audio_files_count'] }} archivos generados</p>
            </div>
            <div class="bg-slate-50 dark:bg-[#071014] rounded-xl p-3.5 border border-slate-200/80 dark:border-cyan-900/30 shadow-inner">
                <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase tracking-wider">Horas de Audio</p>
                <p class="text-2xl font-black text-[#00c965] dark:text-[#00ff87] mt-0.5">{{ $conversionMetrics['total_hours'] }}h</p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $conversionMetrics['total_chapters'] }} pistas sintetizadas</p>
            </div>
            <div class="bg-slate-50 dark:bg-[#071014] rounded-xl p-3.5 border border-slate-200/80 dark:border-cyan-900/30 shadow-inner">
                <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase tracking-wider">Caracteres Sintetizados</p>
                <p class="text-2xl font-black text-teal-600 dark:text-cyan-300 mt-0.5">{{ $conversionMetrics['estimated_characters'] }}</p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $conversionMetrics['total_words'] }} palabras procesadas</p>
            </div>
            <div class="bg-slate-50 dark:bg-[#071014] rounded-xl p-3.5 border border-slate-200/80 dark:border-cyan-900/30 shadow-inner">
                <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase tracking-wider">Tickets Pendientes</p>
                <p class="text-2xl font-black {{ $ticketMetrics['pending_tickets'] > 0 ? 'text-amber-500 dark:text-amber-400' : 'text-slate-900 dark:text-cyan-300' }} mt-0.5">
                    {{ $ticketMetrics['pending_tickets'] }}
                </p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $ticketMetrics['pending_extensions'] }} solicitudes de cuota</p>
            </div>
        </div>
    </div>

    <!-- 2-Column Section: Storage Health & Conversion Pipeline -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <!-- Column 1: Storage & Disk Health -->
        <div class="card-tactile rounded-2xl p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-cyan-950/60 pb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500 dark:text-[#00ff87]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-cyan-200">Almacenamiento del Servidor</h2>
                </div>
                <span class="text-xs font-mono font-semibold text-slate-500 dark:text-sky-400">Total: {{ $storageMetrics['total_app_storage'] }}</span>
            </div>

            <!-- Disk Usage Bar -->
            <div class="space-y-1.5">
                <div class="flex justify-between text-xs font-medium text-slate-600 dark:text-sky-300">
                    <span>Uso del Disco en Servidor</span>
                    <span class="font-mono font-bold">{{ $storageMetrics['disk_usage_percent'] }}% (Libre: {{ $storageMetrics['free_disk'] }})</span>
                </div>
                <div class="w-full h-3 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden border border-slate-200 dark:border-slate-700">
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-500" style="width: {{ min(100, max(5, $storageMetrics['disk_usage_percent'])) }}%"></div>
                </div>
            </div>

            <!-- Storage Items Grid -->
            <div class="grid grid-cols-2 gap-3 pt-2">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071014] border border-slate-200/80 dark:border-cyan-900/30">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase">Carpeta Audiobooks (MP3/WAV)</p>
                    <p class="text-lg font-black text-slate-900 dark:text-cyan-100 mt-1">{{ $storageMetrics['audiobooks_size'] }}</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $storageMetrics['audio_files_count'] }} pistas de audio</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071014] border border-slate-200/80 dark:border-cyan-900/30">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase">Documentos Originales (PDF/DOCX)</p>
                    <p class="text-lg font-black text-slate-900 dark:text-cyan-100 mt-1">{{ $storageMetrics['pdfs_size'] }}</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $storageMetrics['pdf_files_count'] }} documentos guardados</p>
                </div>
            </div>

            <!-- Retention Policy Notice -->
            <div class="p-3 rounded-xl bg-teal-50 dark:bg-teal-950/30 border border-teal-500/20 text-xs text-teal-800 dark:text-teal-200 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-teal-600 dark:text-teal-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="space-y-0.5 leading-snug">
                    <p class="font-bold">Política de Retención Early Access:</p>
                    <p class="text-[11px] text-teal-700 dark:text-teal-300">
                        Los audios generados están disponibles permanentemente salvo depuración anunciada. Los usuarios reciben la indicación de descargar sus archivos localmente.
                    </p>
                </div>
            </div>
        </div>

        <!-- Column 2: User Quotas & Conversion Health -->
        <div class="card-tactile rounded-2xl p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-cyan-950/60 pb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-cyan-200">Adopción & Cuotas de Usuarios</h2>
                </div>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-[#00c965] dark:text-[#00ff87] hover:underline">Gestionar &rarr;</a>
            </div>

            <!-- User Quotas Stats -->
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071014] border border-slate-200/80 dark:border-cyan-900/30">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase">Usuarios Activos</p>
                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $userMetrics['active_users'] }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">de {{ $userMetrics['total_users'] }} total</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071014] border border-slate-200/80 dark:border-cyan-900/30">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase">En el Límite</p>
                    <p class="text-lg font-black {{ $userMetrics['users_at_limit'] > 0 ? 'text-amber-500' : 'text-slate-900 dark:text-white' }} mt-1">
                        {{ $userMetrics['users_at_limit'] }}
                    </p>
                    <p class="text-[10px] text-slate-400 font-mono">cuota completa</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#071014] border border-slate-200/80 dark:border-cyan-900/30">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase">Auto-Extensión</p>
                    <p class="text-lg font-black text-teal-600 dark:text-cyan-300 mt-1">{{ $userMetrics['auto_extensions_used'] }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">cortesías otorgadas</p>
                </div>
            </div>

            <!-- Conversion Pipeline Summary -->
            <div class="space-y-2 pt-1">
                <p class="text-xs font-bold text-slate-700 dark:text-sky-300">Pipeline de Conversión:</p>
                <div class="flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-700 dark:text-[#00ff87] font-semibold border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ $conversionMetrics['ready_books'] }} Completados
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-700 dark:text-amber-400 font-semibold border border-amber-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {{ $conversionMetrics['processing_books'] }} En Cola / Proceso
                    </span>
                    @if ($conversionMetrics['failed_books'] > 0)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-500/10 text-rose-700 dark:text-rose-400 font-semibold border border-rose-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> {{ $conversionMetrics['failed_books'] }} Fallidos
                        </span>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Web Traffic, Audience & Group Sharing Telemetry -->
    <div class="card-tactile rounded-2xl p-5 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200/80 dark:border-cyan-950/60 pb-3">
            <div class="flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-cyan-500/10 text-cyan-500 border border-cyan-500/20">
                    <svg class="w-5 h-5 text-cyan-500 dark:text-cyan-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span> Tráfico en Vivo
                        </span>
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">&bull; Grupos & Enlaces</span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-cyan-200 tracking-tight mt-0.5">
                        Conexiones, Visitas & Audiencia
                    </h2>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-[#071014] border border-slate-200 dark:border-cyan-900/40 text-xs font-mono text-slate-600 dark:text-sky-300">
                    Únicos Hoy: <strong class="text-emerald-600 dark:text-[#00ff87]">{{ $trafficMetrics['unique_today'] }}</strong>
                </span>
            </div>
        </div>

        <!-- 5 Traffic KPI Bento Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#071014] border border-slate-200/80 dark:border-cyan-900/30">
                <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase">Peticiones Totales</p>
                <p class="text-xl font-black text-slate-900 dark:text-cyan-200 mt-0.5">{{ number_format($trafficMetrics['total_hits']) }}</p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ number_format($trafficMetrics['today_hits']) }} registradas hoy</p>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#071014] border border-slate-200/80 dark:border-cyan-900/30">
                <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase">Visitantes Únicos</p>
                <p class="text-xl font-black text-[#00c965] dark:text-[#00ff87] mt-0.5">{{ number_format($trafficMetrics['unique_today']) }}</p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ number_format($trafficMetrics['unique_total']) }} total histórico</p>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#071014] border border-slate-200/80 dark:border-cyan-900/30">
                <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase">Invitados Anónimos</p>
                <p class="text-xl font-black text-cyan-600 dark:text-cyan-300 mt-0.5">{{ number_format($trafficMetrics['guest_hits']) }}</p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">vs. {{ number_format($trafficMetrics['auth_hits']) }} de miembros</p>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#071014] border border-slate-200/80 dark:border-cyan-900/30">
                <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase">Impacto en Grupos</p>
                <p class="text-xl font-black text-amber-500 dark:text-amber-400 mt-0.5">{{ number_format($trafficMetrics['crawler_hits']) }}</p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">previews WhatsApp / bots</p>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#071014] border border-slate-200/80 dark:border-cyan-900/30 col-span-2 sm:col-span-1">
                <p class="text-[10px] font-bold text-slate-400 dark:text-sky-400 uppercase">Tráfico Móvil</p>
                <p class="text-xl font-black text-teal-600 dark:text-cyan-300 mt-0.5">{{ $trafficMetrics['mobile_percent'] }}%</p>
                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $trafficMetrics['mobile_hits'] }} móvil &bull; {{ $trafficMetrics['desktop_hits'] }} PC</p>
            </div>
        </div>

        <!-- 2 Subcolumns: Top Paths & Live Recent Stream -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 pt-2">
            <!-- Top Paths / Enlaces más visitados -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <span>Rutas Más Solicitadas</span>
                </h3>

                <div class="space-y-1.5">
                    @forelse ($trafficMetrics['top_paths'] as $top)
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-[#071014] border border-slate-200/80 dark:border-cyan-900/30 flex items-center justify-between gap-2 text-xs">
                            <span class="font-mono text-slate-800 dark:text-cyan-100 truncate max-w-[200px]" title="{{ $top->path }}">
                                {{ $top->path }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md font-mono font-bold text-[10px] bg-emerald-500/10 text-emerald-700 dark:text-[#00ff87] border border-emerald-500/20">
                                {{ $top->hits }} hits
                            </span>
                        </div>
                    @empty
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#071014] text-xs text-slate-400 italic text-center">
                            Aún no hay rutas registradas.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Live Stream of Recent Visits (2 Columns Wide on Desktop) -->
            <div class="lg:col-span-2 space-y-3">
                <h3 class="text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-cyan-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Últimas 25 Conexiones al Servidor</span>
                </h3>

                <div class="max-h-72 overflow-y-auto space-y-1.5 pr-1">
                    @forelse ($trafficMetrics['recent_visits'] as $visit)
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-[#071014] border border-slate-200/80 dark:border-cyan-900/30 flex items-center justify-between gap-2 text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <!-- Device Icon -->
                                <span class="p-1.5 rounded-lg bg-slate-200/60 dark:bg-slate-800 text-slate-600 dark:text-sky-300 flex-shrink-0" title="{{ $visit->device_type }}">
                                    @if ($visit->is_crawler)
                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    @elseif ($visit->device_type === 'mobile')
                                        <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    @else
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    @endif
                                </span>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono text-[11px] font-bold text-slate-900 dark:text-cyan-200 truncate">
                                            {{ $visit->path }}
                                        </span>
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-mono {{ $visit->status_code < 400 ? 'bg-emerald-500/10 text-emerald-600 dark:text-[#00ff87]' : 'bg-rose-500/10 text-rose-500' }}">
                                            {{ $visit->status_code }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-400 truncate">
                                        @if ($visit->is_crawler)
                                            <span class="text-amber-500 font-bold">Rastreador Social / Bot</span> &bull; preview
                                        @elseif ($visit->user)
                                            <span class="text-emerald-500 font-semibold">{{ $visit->user->name }}</span>
                                        @else
                                            <span class="text-cyan-400 font-semibold">Invitado Anónimo</span>
                                        @endif
                                        @if ($visit->referer)
                                            &bull; desde {{ parse_url($visit->referer, PHP_URL_HOST) ?? $visit->referer }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <span class="text-[10px] text-slate-400 font-mono whitespace-nowrap flex-shrink-0">
                                {{ $visit->created_at->diffForHumans() }}
                            </span>
                        </div>
                    @empty
                        <div class="p-6 rounded-xl bg-slate-50 dark:bg-[#071014] text-xs text-slate-400 italic text-center">
                            Aún no se han registrado conexiones.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Live Server Logs Viewer (Web Monospace Terminal) -->
    <div class="card-tactile rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-200/80 dark:border-cyan-950/60 bg-slate-50/70 dark:bg-[#071014]/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500 dark:text-[#00ff87]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-cyan-200">Visor de Logs en Vivo (storage/logs/laravel.log)</h2>
                    <p class="text-[11px] text-slate-500 dark:text-sky-400">Últimas 70 líneas de depuración sin requerir consola SSH</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.telemetry.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 dark:text-sky-300 hover:bg-slate-200 dark:hover:bg-slate-800 transition inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Actualizar</span>
                </a>
                <form action="{{ route('admin.telemetry.clear-logs') }}" method="POST" onsubmit="return confirm('¿Seguro que deseas vaciar el archivo de logs?');">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 border border-rose-500/30 transition">
                        Vaciar Logs
                    </button>
                </form>
            </div>
        </div>

        <!-- Terminal Body -->
        <div class="p-4 bg-slate-950 text-slate-200 font-mono text-[11px] leading-relaxed max-h-96 overflow-y-auto selection:bg-[#00ff87] selection:text-slate-950">
            @forelse ($logs as $line)
                <div class="py-0.5 {{ str_contains($line, 'ERROR') || str_contains($line, 'Exception') ? 'text-rose-400 font-bold bg-rose-950/20 px-1 rounded' : (str_contains($line, 'INFO') ? 'text-emerald-400' : 'text-slate-300') }}">
                    {{ $line }}
                </div>
            @empty
                <div class="text-slate-500 italic">No hay registros recientes para mostrar.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection
