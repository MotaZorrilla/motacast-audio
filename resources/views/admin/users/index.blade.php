@extends('layouts.app')

@section('title', 'Gestión de Usuarios - Admin MotaCastAudio')

@section('content')
<div class="space-y-6">

    <!-- Admin Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3 overflow-x-auto">
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 dark:bg-indigo-950/50 border border-indigo-500/20 transition inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
        <a href="{{ route('admin.tickets.index') }}" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span>Tickets & Soporte</span>
        </a>
    </div>

    <!-- Top Header & Metrics Banner -->
    <div class="card-tactile rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#090d16] p-5 sm:p-6 shadow-sm relative overflow-hidden">
        <!-- Neon decorative background glow -->
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Panel Admin
                    </span>
                    <span class="text-xs text-slate-400 dark:text-slate-500 font-mono">&bull; Cuotas & Control de Acceso</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Gestión de Usuarios y Límites</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Supervisa usuarios, ajusta cuotas de documentos, suspende o asigna permisos administrativos.
                </p>
            </div>

            <!-- Create User Trigger Button -->
            <button 
                type="button" 
                onclick="openCreateUserModal()" 
                class="btn-primary-tactile inline-flex items-center justify-center gap-2 px-4 py-2.5 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md transition flex-shrink-0"
            >
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nuevo Usuario</span>
            </button>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-5 pt-5 border-t border-slate-200/80 dark:border-slate-800 relative z-10">
            <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800">
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Usuarios</p>
                <p class="text-2xl font-extrabold text-slate-900 dark:text-white mt-0.5">{{ $stats['total_users'] }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800">
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Activos</p>
                <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $stats['active_users'] }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800">
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Suspendidos</p>
                <p class="text-2xl font-extrabold text-rose-500 dark:text-rose-400 mt-0.5">{{ $stats['suspended_users'] }}</p>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800">
                <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Administradores</p>
                <p class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $stats['admins'] }}</p>
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="card-tactile rounded-xl border border-slate-200 dark:border-slate-800 p-3.5 bg-white dark:bg-[#090d16] shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form action="{{ route('admin.users.index') }}" method="GET" class="w-full md:w-auto flex-grow max-w-md">
            <div class="relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Buscar por nombre o correo electrónico..."
                    class="w-full pl-9 pr-4 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition"
                >
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </form>

        <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
            <!-- Filter by Role -->
            <div class="inline-flex rounded-lg p-0.5 bg-slate-100 dark:bg-slate-900 text-xs">
                <a href="{{ route('admin.users.index', array_merge(request()->except('role'), ['role' => ''])) }}" class="px-2.5 py-1 rounded-md font-medium {{ !request('role') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400' }}">
                    Todos
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->except('role'), ['role' => 'admin'])) }}" class="px-2.5 py-1 rounded-md font-medium {{ request('role') === 'admin' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400' }}">
                    Admins
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->except('role'), ['role' => 'user'])) }}" class="px-2.5 py-1 rounded-md font-medium {{ request('role') === 'user' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400' }}">
                    Usuarios
                </a>
            </div>

            <!-- Filter by Status -->
            <div class="inline-flex rounded-lg p-0.5 bg-slate-100 dark:bg-slate-900 text-xs">
                <a href="{{ route('admin.users.index', array_merge(request()->except('status'), ['status' => ''])) }}" class="px-2.5 py-1 rounded-md font-medium {{ !request('status') ? 'bg-slate-700 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400' }}">
                    Estado: Todos
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->except('status'), ['status' => 'active'])) }}" class="px-2.5 py-1 rounded-md font-medium {{ request('status') === 'active' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400' }}">
                    Activos
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->except('status'), ['status' => 'suspended'])) }}" class="px-2.5 py-1 rounded-md font-medium {{ request('status') === 'suspended' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400' }}">
                    Suspendidos
                </a>
            </div>
        </div>
    </div>

    <!-- Users Table (Desktop) & Cards (Mobile) -->
    <div class="card-tactile rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm bg-white dark:bg-[#090d16]">
        
        <!-- Desktop Table -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-900/60 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-4">Usuario</th>
                        <th class="py-3 px-4">Rol & Estado</th>
                        <th class="py-3 px-4">Libros Cargados</th>
                        <th class="py-3 px-4">Cuota Permitida</th>
                        <th class="py-3 px-4 text-center">Ajustar Cuota Rápida</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-900/50 transition">
                            <!-- User info -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $user->isAdmin() ? 'from-indigo-600 to-indigo-800 text-white font-black' : 'from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-800 text-slate-700 dark:text-slate-300 font-bold' }} flex items-center justify-center flex-shrink-0 shadow-sm text-xs">
                                        {{ mb_strtoupper(mb_substr($user->name, 0, 2, 'UTF-8')) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-slate-900 dark:text-white truncate max-w-[180px]">{{ $user->name }}</span>
                                            @if ($user->id === Auth::id())
                                                <span class="px-1.5 py-0.2 text-[9px] font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 rounded">TÚ</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate max-w-[200px]">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Role & Status -->
                            <td class="py-3 px-4">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold w-fit {{ $user->isAdmin() ? 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                                        {{ $user->isAdmin() ? 'Administrador' : 'Usuario Estándar' }}
                                    </span>
                                    @if ($user->status === 'active')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold w-fit bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold w-fit bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Suspendido
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Books count & Audit Link -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $user->books_count }}</span>
                                    <a href="{{ route('books.index', ['user_id' => $user->id]) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 hover:bg-emerald-50 dark:hover:bg-emerald-950 text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 transition text-[10px] font-semibold" title="Auditar libros de este usuario">
                                        <span>Auditar</span>
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </div>
                            </td>

                            <!-- Quota -->
                            <td class="py-3 px-4">
                                @if ($user->book_limit === -1)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        &infin; Ilimitado
                                    </span>
                                @else
                                    <div class="space-y-0.5">
                                        <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $user->books_count }} / {{ $user->book_limit }}</span>
                                        <div class="w-20 bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                            @php
                                                $pct = $user->book_limit > 0 ? min(100, round(($user->books_count / $user->book_limit) * 100)) : 100;
                                            @endphp
                                            <div class="h-1.5 rounded-full {{ $pct >= 100 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            </td>

                            <!-- Quick Quota Adjusters -->
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-center gap-1">
                                    <form action="{{ route('admin.users.adjust-limit', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="plus1">
                                        <button type="submit" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-600 hover:text-white rounded text-[10px] font-bold font-mono transition" title="Agregar +1 a su cuota">+1</button>
                                    </form>
                                    <form action="{{ route('admin.users.adjust-limit', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="plus5">
                                        <button type="submit" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-600 hover:text-white rounded text-[10px] font-bold font-mono transition" title="Agregar +5 a su cuota">+5</button>
                                    </form>
                                    <form action="{{ route('admin.users.adjust-limit', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="unlimited">
                                        <button type="submit" class="px-2 py-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 hover:bg-indigo-600 hover:text-white rounded text-[10px] font-bold font-mono border border-emerald-300 dark:border-emerald-800 transition" title="Hacer cuota ilimitada">&infin;</button>
                                    </form>
                                    <form action="{{ route('admin.users.adjust-limit', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="reset">
                                        <button type="submit" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 rounded text-[10px] font-mono transition" title="Restablecer cuota estándar (3)">3</button>
                                    </form>
                                </div>
                            </td>

                            <!-- Actions Menu -->
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Toggle Status Button -->
                                    @if ($user->id !== Auth::id())
                                        <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button 
                                                type="submit" 
                                                class="p-1.5 rounded-lg {{ $user->status === 'active' ? 'text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/30' : 'text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-950/30' }} transition"
                                                title="{{ $user->status === 'active' ? 'Suspender usuario' : 'Activar usuario' }}"
                                                onclick="return confirm('¿Deseas cambiar el estado de este usuario?');"
                                            >
                                                @if ($user->status === 'active')
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Edit User Button -->
                                    <button 
                                        type="button" 
                                        onclick="openEditUserModal({{ json_encode($user) }})"
                                        class="p-1.5 text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition"
                                        title="Editar datos de usuario"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <!-- Cascade Delete Button -->
                                    @if ($user->id !== Auth::id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('ATENCIÓN: ¿Seguro que deseas eliminar al usuario \'{{ $user->name }}\'? Se eliminarán de forma permanente todos sus {{ $user->books_count }} audiolibros y archivos del servidor.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition" title="Eliminar usuario y sus archivos en cascada">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                No se encontraron usuarios coincidentes con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List (Ergonomic for Thumb Scrolling) -->
        <div class="block lg:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
            @forelse ($users as $user)
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $user->isAdmin() ? 'from-emerald-500 to-teal-700 text-slate-950 font-black' : 'from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-800 text-slate-700 dark:text-slate-300 font-bold' }} flex items-center justify-center flex-shrink-0 shadow-sm text-sm">
                                {{ mb_strtoupper(mb_substr($user->name, 0, 2, 'UTF-8')) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <span>{{ $user->name }}</span>
                                    @if ($user->id === Auth::id())
                                        <span class="px-1.5 py-0.2 text-[8px] font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 rounded">TÚ</span>
                                    @endif
                                </h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500">{{ $user->email }}</p>
                            </div>
                        </div>

                        <!-- Status badge -->
                        @if ($user->status === 'active')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Suspendido
                            </span>
                        @endif
                    </div>

                    <!-- Quota & Book count info -->
                    <div class="flex items-center justify-between text-xs bg-slate-50 dark:bg-slate-900/60 rounded-xl p-2.5 border border-slate-100 dark:border-slate-700/60">
                        <div>
                            <span class="text-slate-400 block text-[10px]">Libros cargados:</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $user->books_count }}</span>
                            <a href="{{ route('books.index', ['user_id' => $user->id]) }}" class="text-emerald-600 dark:text-emerald-400 font-semibold text-[11px] underline ml-1">Ver todos</a>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-400 block text-[10px]">Límite permitido:</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">
                                {{ $user->book_limit === -1 ? 'Ilimitado' : "{$user->book_limit} libros" }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions Bar for Mobile -->
                    <div class="flex items-center justify-between pt-1">
                        <!-- Quota adjusters -->
                        <div class="flex items-center gap-1">
                            <span class="text-[10px] font-bold text-slate-400 mr-0.5">Cuota:</span>
                            <form action="{{ route('admin.users.adjust-limit', $user->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="plus1">
                                <button type="submit" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-600 hover:text-white rounded text-[10px] font-bold font-mono">+1</button>
                            </form>
                            <form action="{{ route('admin.users.adjust-limit', $user->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="plus5">
                                <button type="submit" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-600 hover:text-white rounded text-[10px] font-bold font-mono">+5</button>
                            </form>
                            <form action="{{ route('admin.users.adjust-limit', $user->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="action" value="unlimited">
                                <button type="submit" class="px-2 py-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 rounded text-[10px] font-bold font-mono border border-emerald-300 dark:border-emerald-800">&infin;</button>
                            </form>
                        </div>

                        <!-- Edit & Delete buttons -->
                        <div class="flex items-center gap-1">
                            <button 
                                type="button" 
                                onclick="openEditUserModal({{ json_encode($user) }})"
                                class="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs rounded-lg"
                            >
                                Editar
                            </button>

                            @if ($user->id !== Auth::id())
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar al usuario \'{{ $user->name }}\' y todos sus audiolibros?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-rose-500 hover:bg-rose-50 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                    No se encontraron usuarios coincidentes.
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="p-4 border-t border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal: Crear Usuario -->
<div id="createUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/70 backdrop-blur-sm p-4 sm:p-6 flex items-center justify-center">
    <div class="card-tactile bg-white dark:bg-[#090d16] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Crear Nuevo Usuario</span>
            </h3>
            <button type="button" onclick="closeCreateUserModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="create_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Nombre Completo *</label>
                <input type="text" name="name" id="create_name" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div>
                <label for="create_email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Correo Electrónico *</label>
                <input type="email" name="email" id="create_email" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div>
                <label for="create_password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Contraseña Inicial *</label>
                <input type="password" name="password" id="create_password" required minlength="6" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="create_role" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Rol</label>
                    <select name="role" id="create_role" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="user" selected>Usuario</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <div>
                    <label for="create_status" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Estado</label>
                    <select name="status" id="create_status" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="active" selected>Activo</option>
                        <option value="suspended">Suspendido</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="create_book_limit" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Límite de Audiolibros (-1 = Ilimitado)</label>
                <input type="number" name="book_limit" id="create_book_limit" value="3" min="-1" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeCreateUserModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancelar</button>
                <button type="submit" class="btn-primary-tactile px-4 py-2 text-xs font-bold text-white rounded-xl shadow-md transition">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar Usuario -->
<div id="editUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/70 backdrop-blur-sm p-4 sm:p-6 flex items-center justify-center">
    <div class="card-tactile bg-white dark:bg-[#090d16] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Editar Usuario</span>
            </h3>
            <button type="button" onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="editUserForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="edit_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Nombre Completo *</label>
                <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div>
                <label for="edit_email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Correo Electrónico *</label>
                <input type="email" name="email" id="edit_email" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div>
                <label for="edit_password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Nueva Contraseña (Opcional)</label>
                <input type="password" name="password" id="edit_password" minlength="6" placeholder="Dejar en blanco para conservar la actual" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="edit_role" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Rol</label>
                    <select name="role" id="edit_role" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="user">Usuario</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <div>
                    <label for="edit_status" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Estado</label>
                    <select name="status" id="edit_status" class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="active">Activo</option>
                        <option value="suspended">Suspendido</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="edit_book_limit" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase mb-1">Límite de Audiolibros (-1 = Ilimitado)</label>
                <input type="number" name="book_limit" id="edit_book_limit" min="-1" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl">Cancelar</button>
                <button type="submit" class="btn-primary-tactile px-4 py-2 text-xs font-bold text-white rounded-xl shadow-md transition">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCreateUserModal() {
        document.getElementById('createUserModal').classList.remove('hidden');
    }
    function closeCreateUserModal() {
        document.getElementById('createUserModal').classList.add('hidden');
    }

    function openEditUserModal(user) {
        const form = document.getElementById('editUserForm');
        form.action = "{{ url('admin/users') }}/" + user.id;

        document.getElementById('edit_name').value = user.name || '';
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_role').value = user.role || 'user';
        document.getElementById('edit_status').value = user.status || 'active';
        document.getElementById('edit_book_limit').value = user.book_limit !== undefined ? user.book_limit : 3;

        document.getElementById('editUserModal').classList.remove('hidden');
    }
    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }

    // Close on backdrop click
    window.addEventListener('click', function(e) {
        const createModal = document.getElementById('createUserModal');
        const editModal = document.getElementById('editUserModal');
        if (e.target === createModal) closeCreateUserModal();
        if (e.target === editModal) closeEditUserModal();
    });
</script>
@endpush
@endsection
