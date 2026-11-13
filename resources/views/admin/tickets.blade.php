@extends('layouts.app')

@section('title', 'Tickets & Soporte - Admin MotaCastAudio')

@section('content')
<div class="space-y-6">

    <!-- Admin Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 overflow-x-auto">
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Usuarios & Cuotas</span>
        </a>
        <a href="{{ route('admin.telemetry.index') }}" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Telemetría & Métricas</span>
        </a>
        <a href="{{ route('admin.tickets.index') }}" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 dark:bg-indigo-950/50 border border-indigo-500/20 transition inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span>Tickets & Soporte</span>
            @if ($stats['pending'] > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-500 text-slate-950">
                    {{ $stats['pending'] }}
                </span>
            @endif
        </a>
    </div>

    <!-- Header & Metrics Banner -->
    <div class="card-tactile rounded-2xl p-5 sm:p-6 relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Bandeja de Soporte
                    </span>
                    <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">&bull; Consultas & Ampliaciones</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Centro de Tickets y Consultas</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Responde mensajes de usuarios Beta, revisa reportes de bugs y aprueba ampliaciones de cuota con 1 clic.
                </p>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5 pt-5 border-t border-slate-200/80 dark:border-slate-800 relative z-10">
            <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800">
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Total Mensajes</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800">
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Pendientes</p>
                <p class="text-2xl font-black {{ $stats['pending'] > 0 ? 'text-amber-500 dark:text-amber-400' : 'text-slate-900 dark:text-white' }} mt-0.5">
                    {{ $stats['pending'] }}
                </p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800">
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Solicitudes de Cuota</p>
                <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $stats['extensions_pending'] }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800">
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Resueltos</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $stats['resolved'] }}</p>
            </div>
        </div>
    </div>

    <!-- Filters Toolbar -->
    <div class="card-tactile rounded-xl p-3.5 flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.tickets.index') }}" method="GET" class="w-full md:w-auto flex-grow max-w-md">
            <div class="relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Buscar por asunto, usuario o mensaje..."
                    class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                >
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </form>

        <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
            <!-- Filter by Type -->
            <div class="inline-flex rounded-lg p-0.5 bg-slate-100 dark:bg-slate-900 text-xs">
                <a href="{{ route('admin.tickets.index', array_merge(request()->query(), ['type' => ''])) }}" class="px-2.5 py-1 rounded-md font-semibold {{ !request('type') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                    Todos
                </a>
                <a href="{{ route('admin.tickets.index', array_merge(request()->query(), ['type' => 'extension_limite'])) }}" class="px-2.5 py-1 rounded-md font-semibold {{ request('type') === 'extension_limite' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                    Cuotas
                </a>
                <a href="{{ route('admin.tickets.index', array_merge(request()->query(), ['type' => 'bug'])) }}" class="px-2.5 py-1 rounded-md font-semibold {{ request('type') === 'bug' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                    Bugs
                </a>
                <a href="{{ route('admin.tickets.index', array_merge(request()->query(), ['type' => 'sugerencia'])) }}" class="px-2.5 py-1 rounded-md font-semibold {{ request('type') === 'sugerencia' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                    Sugerencias
                </a>
            </div>

            <!-- Filter by Status -->
            <div class="inline-flex rounded-lg p-0.5 bg-slate-100 dark:bg-slate-900 text-xs">
                <a href="{{ route('admin.tickets.index', array_merge(request()->query(), ['status' => 'pendiente'])) }}" class="px-2.5 py-1 rounded-md font-semibold {{ request('status') === 'pendiente' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                    Pendientes
                </a>
                <a href="{{ route('admin.tickets.index', array_merge(request()->query(), ['status' => 'resuelto'])) }}" class="px-2.5 py-1 rounded-md font-semibold {{ request('status') === 'resuelto' ? 'bg-emerald-500 text-slate-950 shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                    Resueltos
                </a>
            </div>
        </div>
    </div>

    <!-- Tickets List Table / Mobile Cards -->
    <div class="card-tactile rounded-2xl overflow-hidden">
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($tickets as $ticket)
                <div class="p-4 sm:p-5 space-y-3 hover:bg-slate-50/50 dark:hover:bg-slate-900/50 transition">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase border {{ $ticket->status_badge_classes }}">
                                {{ $ticket->status }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800">
                                {{ $ticket->formatted_type }}
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                                {{ $ticket->subject }}
                            </h3>
                        </div>

                        <div class="text-xs text-slate-400 font-mono">
                            {{ $ticket->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <!-- User and Message -->
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                            <strong>Usuario:</strong> 
                            @if ($ticket->user)
                                <span>{{ $ticket->user->name }} ({{ $ticket->user->email }}) &bull; Cuota actual: <strong>{{ $ticket->user->book_limit === -1 ? '∞' : $ticket->user->book_limit }} libros</strong></span>
                            @else
                                <span>Invitado: {{ $ticket->guest_email }}</span>
                            @endif
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-800 dark:text-slate-200 leading-relaxed whitespace-pre-wrap">
                            {{ $ticket->message }}
                        </div>
                    </div>

                    <!-- Admin Reply Display if exists -->
                    @if ($ticket->admin_reply)
                        <div class="p-3 rounded-xl bg-indigo-500/10 dark:bg-indigo-950/30 border border-indigo-500/20 text-xs space-y-1">
                            <p class="font-bold text-indigo-800 dark:text-indigo-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                Respuesta del Administrador:
                            </p>
                            <p class="text-slate-700 dark:text-slate-300 leading-relaxed">{{ $ticket->admin_reply }}</p>
                        </div>
                    @endif

                    <!-- Action Bar -->
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <!-- 1-Click Approve Quota Buttons if extension requested -->
                            @if ($ticket->type === 'extension_limite' && $ticket->status === 'pendiente' && $ticket->user)
                                <form action="{{ route('admin.tickets.approve-extension', $ticket->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="books" value="2">
                                    <button type="submit" class="btn-primary-tactile px-3 py-1.5 rounded-xl text-xs font-black shadow-sm transition text-white">
                                        ⚡ Aprobar +2 Libros
                                    </button>
                                </form>
                                <form action="{{ route('admin.tickets.approve-extension', $ticket->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="books" value="5">
                                    <button type="submit" class="px-3 py-1.5 bg-teal-600 hover:bg-teal-500 text-white rounded-xl text-xs font-bold transition">
                                        Aprobar +5 Libros
                                    </button>
                                </form>
                            @endif

                            <!-- Open Reply Modal Button -->
                            <button 
                                type="button" 
                                onclick="openReplyModal({{ $ticket->id }}, '{{ $ticket->status }}', '{{ addslashes($ticket->subject) }}', '{{ addslashes($ticket->admin_reply ?? '') }}')"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition"
                            >
                                {{ $ticket->admin_reply ? 'Editar Respuesta' : 'Responder / Cambiar Estado' }}
                            </button>
                        </div>

                        <!-- Delete Ticket -->
                        <form action="{{ route('admin.tickets.destroy', $ticket->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este ticket permanentemente?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-500 rounded-lg transition" title="Eliminar Ticket">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                    No hay tickets ni notas registradas con los filtros actuales.
                </div>
            @endforelse
        </div>

        @if ($tickets->hasPages())
            <div class="p-4 border-t border-slate-200/80 dark:border-slate-800">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Admin Reply Modal -->
<div id="replyTicketModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="card-tactile w-full max-w-lg rounded-3xl p-6 space-y-4 relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-900 dark:text-white" id="replyModalTitle">Responder Ticket</h3>
            <button type="button" onclick="closeReplyModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form id="replyTicketForm" method="POST" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">Estado del Ticket</label>
                <select name="status" id="modalTicketStatus" class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="pendiente">Pendiente</option>
                    <option value="en_revision">En Revisión</option>
                    <option value="resuelto">Resuelto</option>
                    <option value="rechazado">Rechazado</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">Respuesta al Usuario</label>
                <textarea name="admin_reply" id="modalAdminReply" rows="4" required placeholder="Escribe tu respuesta o explicación..." class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 placeholder-slate-400"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeReplyModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary-tactile px-5 py-2 rounded-xl text-xs font-black shadow-sm text-white">
                    Guardar Respuesta
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReplyModal(ticketId, currentStatus, subject, existingReply) {
        const form = document.getElementById('replyTicketForm');
        form.action = `{{ url('/admin/tickets') }}/${ticketId}/reply`;
        document.getElementById('replyModalTitle').textContent = `Responder: ${subject}`;
        document.getElementById('modalTicketStatus').value = currentStatus;
        document.getElementById('modalAdminReply').value = existingReply || '';
        document.getElementById('replyTicketModal').classList.remove('hidden');
    }

    function closeReplyModal() {
        document.getElementById('replyTicketModal').classList.add('hidden');
    }
</script>
@endsection
