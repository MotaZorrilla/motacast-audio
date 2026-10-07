<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MotaCastAudio') - Mota Zorrilla</title>

    <!-- Standard SEO & Description Meta -->
    <meta name="description" content="@yield('meta_description', 'MotaCastAudio convierte PDFs, documentos de Word, texto e imágenes en audiolibros con voces neuronales realistas, resúmenes ejecutivos automáticos y reproductor interactivo.')">
    <meta name="author" content="@yield('meta_author', 'Héctor Mota Zorrilla')">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / WhatsApp / Facebook / LinkedIn Meta Tags -->
    <meta property="og:site_name" content="MotaCastAudio">
    <meta property="og:title" content="@yield('og_title', 'MotaCastAudio - Documentos a Audiolibros con IA')">
    <meta property="og:description" content="@yield('og_description', 'Convierte al instante documentos PDF, Word, texto e imágenes (OCR) en audiolibros con voces neuronales ultranaturales en español, sinopsis inteligente y reproductor sincronizado.')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/motacast-og-banner.jpg'))">
    <meta property="og:image:secure_url" content="@yield('og_image', asset('images/motacast-og-banner.jpg'))">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="@yield('og_image_alt', 'MotaCastAudio - Documentos a Audiolibros con Inteligencia Artificial')">
    <meta property="og:locale" content="es_LA">

    <!-- Twitter / X Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'MotaCastAudio - Documentos a Audiolibros con IA')">
    <meta name="twitter:description" content="@yield('og_description', 'Convierte PDFs, Word, texto e imágenes en audiolibros interactivos con voces neuronales.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/motacast-og-banner.jpg'))">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/icono.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icono.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Compiled Production Vite Assets (Zero CDN warnings) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Instant Dark Mode Init Script (Prevents White Flash) -->
    <script>
        if (localStorage.getItem('motacast_theme') === 'dark' || 
            (!('motacast_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @stack('styles')
</head>
<body class="flex flex-col min-h-full bg-slate-50 dark:bg-[#070b0d] text-slate-800 dark:text-sky-100 antialiased selection:bg-[#00ff87] selection:text-slate-950 transition-colors duration-200 relative overflow-x-hidden">

    <!-- Interactive Ambient Canvas Background (High-Performance 60FPS Particles & Dynamic Mesh) -->
    <canvas id="ambientCanvas" class="fixed inset-0 pointer-events-none z-0 opacity-40 dark:opacity-30"></canvas>

    <!-- Top Technical Header -->
    <header class="bg-white/90 dark:bg-[#080d0f]/90 backdrop-blur-md border-b border-slate-200/90 dark:border-cyan-900/40 sticky top-0 z-40 shadow-sm transition-colors relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Brand Signature with Bespoke MotaCastAudio Vector Icon -->
                <div class="flex items-center gap-3">
                    <a href="{{ Auth::check() ? route('books.index') : route('home') }}" class="flex items-center gap-3 group">
                        <!-- Bespoke Squircle Audio Icon with Neon Halo -->
                        <div class="relative flex-shrink-0">
                            <img src="{{ asset('images/motacast-icon.svg') }}" 
                                 alt="MotaCastAudio" 
                                 class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl object-contain drop-shadow-[0_2px_10px_rgba(0,255,135,0.45)] group-hover:scale-105 transition"
                                 onerror="this.onerror=null; this.src='{{ asset('images/icono.png') }}';">
                            <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-[#00ff87] ring-2 ring-white dark:ring-[#080d0f]"></span>
                        </div>

                        <div class="border-l border-slate-200 dark:border-cyan-900/60 pl-3">
                            <div class="flex items-center gap-1.5">
                                <span class="text-base sm:text-lg font-black tracking-tight text-slate-900 dark:text-cyan-300 group-hover:text-cyan-400 transition">
                                    MotaCast<span class="text-[#00c965] dark:text-[#00ff87] drop-shadow-[0_0_8px_rgba(0,255,135,0.4)]">Audio</span>
                                </span>
                                <span class="hidden xs:inline-block px-1.5 py-0.5 rounded-md text-[9px] font-mono font-bold bg-slate-100 dark:bg-cyan-950/70 text-slate-600 dark:text-cyan-300 border border-slate-300/80 dark:border-cyan-800/40">v0.9.0-beta</span>
                            </div>
                            <p class="text-[10px] font-mono text-slate-500 dark:text-sky-400 hidden sm:block">Streaming & Audiolibros Personales</p>
                        </div>
                    </a>
                </div>

                <!-- Right Actions: Navigation, Theme Toggle, User Auth Menu -->
                <div class="flex items-center gap-2 sm:gap-3">
                    
                    @auth
                        <!-- Desktop Navigation Links -->
                        <div class="hidden sm:flex items-center gap-1.5">
                            <a href="{{ route('books.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ request()->routeIs('books.index') && !request()->routeIs('admin.*') ? 'text-slate-950 dark:text-cyan-300 bg-slate-100 dark:bg-cyan-950/40 border border-slate-300 dark:border-cyan-500/30' : 'text-slate-600 dark:text-sky-300 hover:bg-slate-100 dark:hover:bg-slate-800' }} transition">
                                Mis Libros
                            </a>

                            @if (Auth::user()->isAdmin())
                                <a href="{{ route('admin.users.index') }}" class="px-2.5 py-1.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.users.*') ? 'text-teal-700 dark:text-cyan-300 bg-teal-500/10 dark:bg-cyan-950/40 border border-teal-500/30 dark:border-cyan-500/30' : 'text-slate-600 dark:text-sky-300 hover:bg-slate-100 dark:hover:bg-slate-800' }} transition inline-flex items-center gap-1" title="Usuarios">
                                    <span>Usuarios</span>
                                </a>
                                <a href="{{ route('admin.telemetry.index') }}" class="px-2.5 py-1.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.telemetry.*') ? 'text-teal-700 dark:text-cyan-300 bg-teal-500/10 dark:bg-cyan-950/40 border border-teal-500/30 dark:border-cyan-500/30' : 'text-slate-600 dark:text-sky-300 hover:bg-slate-100 dark:hover:bg-slate-800' }} transition inline-flex items-center gap-1" title="Telemetría">
                                    <span>Telemetría</span>
                                </a>
                                <a href="{{ route('admin.tickets.index') }}" class="px-2.5 py-1.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.tickets.*') ? 'text-teal-700 dark:text-cyan-300 bg-teal-500/10 dark:bg-cyan-950/40 border border-teal-500/30 dark:border-cyan-500/30' : 'text-slate-600 dark:text-sky-300 hover:bg-slate-100 dark:hover:bg-slate-800' }} transition inline-flex items-center gap-1" title="Tickets">
                                    <span>Tickets</span>
                                </a>
                            @endif

                            <button type="button" onclick="openSupportModal()" class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-slate-600 dark:text-sky-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition inline-flex items-center gap-1" title="Enviar nota o consulta al Administrador">
                                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                <span>Soporte</span>
                            </button>

                            <a href="{{ route('books.create') }}" class="btn-neon-tactile inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black transition">
                                <svg class="w-3.5 h-3.5 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Agregar</span>
                            </a>
                        </div>
                    @endauth

                    <!-- Dark Mode Toggle Button -->
                    <button 
                        type="button" 
                        id="themeToggleBtn"
                        class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700 border border-transparent dark:border-slate-700/60 transition"
                        title="Alternar Modo Claro / Oscuro"
                    >
                        <!-- Sun Icon (shown in dark mode) -->
                        <svg id="sunIcon" class="w-4 h-4 hidden dark:block text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Moon Icon (shown in light mode) -->
                        <svg id="moonIcon" class="w-4 h-4 block dark:hidden text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    @auth
                        <!-- User Profile Badge & Logout -->
                        <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
                            <div class="text-right hidden md:block">
                                <p class="text-xs font-bold text-slate-900 dark:text-white leading-tight">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] font-mono font-semibold uppercase {{ Auth::user()->isAdmin() ? 'text-teal-500' : 'text-[#00c965] dark:text-[#00ff87]' }}">
                                    {{ Auth::user()->role === 'admin' ? 'Administrador' : 'Usuario' }}
                                </p>
                            </div>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition" title="Cerrar Sesión">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @else
                        @if (session('guest_book_id'))
                            <a href="{{ route('books.show', session('guest_book_id')) }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-emerald-700 dark:text-[#00ff87] bg-emerald-500/10 border border-emerald-500/30 hover:bg-emerald-500/20 transition">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#00ff87] animate-pulse"></span>
                                <span>Mi Libro</span>
                            </a>
                        @endif
                        <a href="{{ route('login') }}" class="px-3.5 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-[#00ff87] transition">
                            Entrar
                        </a>
                        <a href="{{ route('register') }}" class="btn-neon-tactile px-3.5 py-1.5 rounded-xl text-xs font-black transition">
                            Registro
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Notifications Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-3 w-full">
        @if (session('success'))
            <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-500/30 text-emerald-800 dark:text-emerald-300 flex items-center justify-between shadow-sm text-xs font-semibold">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-[#00ff87] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-500/30 text-rose-800 dark:text-rose-300 flex items-center justify-between shadow-sm text-xs font-semibold">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if (session('info'))
            <div class="p-3.5 rounded-xl bg-teal-50 dark:bg-teal-950/40 border border-teal-500/30 text-teal-800 dark:text-teal-300 flex items-center justify-between shadow-sm text-xs font-semibold">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-teal-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-500/30 text-amber-900 dark:text-amber-200 shadow-sm text-xs">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Por favor verifica los siguientes campos:</span>
                </div>
                <ul class="list-disc pl-6 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Stage -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 w-full pb-28 sm:pb-12">
        @yield('content')
    </main>

    <!-- Mobile-First Bottom Navigation Dock (Fixed for Ergonomic Thumb Interaction) -->
    @auth
    @if (!request()->routeIs('books.show'))
    <nav class="sm:hidden fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-[#080d0f]/95 backdrop-blur-lg border-t border-slate-200 dark:border-cyan-950/60 z-40 px-2 py-2 flex items-center justify-around shadow-2xl">
        <!-- Tab 1: Mis Libros -->
        <a href="{{ route('books.index') }}" class="flex flex-col items-center gap-1 py-1 px-2 rounded-xl {{ request()->routeIs('books.index') && !request()->routeIs('admin.*') ? 'text-[#00c965] dark:text-[#00ff87] font-bold' : 'text-slate-500 dark:text-sky-400' }} transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span class="text-[9px]">Libros</span>
        </a>

        <!-- Admin Links or User Support -->
        @if (Auth::user()->isAdmin())
            <a href="{{ route('admin.telemetry.index') }}" class="flex flex-col items-center gap-1 py-1 px-2 rounded-xl {{ request()->routeIs('admin.*') ? 'text-teal-600 dark:text-cyan-300 font-bold' : 'text-slate-500 dark:text-sky-400' }} transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-[9px]">Métricas</span>
            </a>
        @else
            <button type="button" onclick="openSupportModal()" class="flex flex-col items-center gap-1 py-1 px-2 rounded-xl text-slate-500 dark:text-sky-400 hover:text-amber-500 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <span class="text-[9px]">Soporte</span>
            </button>
        @endif

        <!-- Tab Central: + Agregar Documento (Destacado y Radiante 3D) -->
        <a href="{{ route('books.create') }}" class="flex flex-col items-center justify-center w-11 h-11 -mt-4 btn-neon-tactile rounded-2xl shadow-neon-md transition transform active:scale-95" title="Agregar nuevo documento">
            <svg class="w-5 h-5 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
        </a>

        @if (Auth::user()->isAdmin())
            <a href="{{ route('admin.tickets.index') }}" class="flex flex-col items-center gap-1 py-1 px-2 rounded-xl {{ request()->routeIs('admin.tickets.*') ? 'text-teal-600 dark:text-cyan-300 font-bold' : 'text-slate-500 dark:text-sky-400' }} transition relative">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <span class="text-[9px]">Tickets</span>
            </a>
        @endif

        <!-- Tab: Reproductor / Streaming con Estado Reactivo -->
        <button type="button" id="mobileAudioDockBtn" onclick="handleDockAudioClick(event)" class="flex flex-col items-center gap-1 py-1 px-2 rounded-xl text-slate-500 dark:text-sky-400 hover:text-[#00c965] dark:hover:text-[#00ff87] transition cursor-pointer" title="Control de Audio">
            <div id="dockSoundwave" class="flex items-center gap-0.5 h-4 transition-all">
                <span class="w-0.5 bg-[#00ff87] rounded-full bar-wave-1 transition-all duration-200"></span>
                <span class="w-0.5 bg-[#00ff87] rounded-full bar-wave-2 transition-all duration-200"></span>
                <span class="w-0.5 bg-[#00ff87] rounded-full bar-wave-3 transition-all duration-200"></span>
                <span class="w-0.5 bg-[#00ff87] rounded-full bar-wave-4 transition-all duration-200"></span>
            </div>
            <span id="dockAudioLabel" class="text-[9px]">Audio</span>
        </button>
    </nav>
    @endif
    @endauth

    <!-- Clean, Friendly Footer: Displayed on Welcome / Public Pages and Library (hidden on show to avoid player console clash) -->
    @if (!request()->routeIs('books.show'))
    <footer class="bg-white/80 dark:bg-[#080d0f]/80 backdrop-blur-md border-t border-slate-200/80 dark:border-cyan-950/50 py-4 mt-auto relative z-10 w-full overflow-hidden pb-20 sm:pb-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/motacast-icon.svg') }}" alt="MotaCastAudio" class="w-5 h-5 object-contain" onerror="this.onerror=null; this.src='{{ asset('images/icono.png') }}';">
                <span class="font-black text-slate-900 dark:text-cyan-300 tracking-tight">MotaCast<span class="text-[#00c965] dark:text-[#00ff87]">Audio</span></span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-50 dark:bg-cyan-950/70 text-emerald-700 dark:text-[#00ff87] border border-emerald-200 dark:border-cyan-800/50 shadow-sm">v0.9.0-beta</span>
            </div>
            
            <div class="text-slate-500 dark:text-sky-300 text-center sm:text-right text-[11px] leading-relaxed max-w-full">
                <span>Diseñado para escuchar y estudiar en movimiento por</span>
                <a href="https://neobranding.cl" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-slate-900 dark:text-cyan-300 hover:text-emerald-500 font-bold transition ml-1">
                    <img src="{{ asset('images/neobranding-isotipo.png') }}" alt="neobranding.cl" class="w-3.5 h-3.5 object-contain filter drop-shadow-[0_0_6px_rgba(0,240,255,0.4)]">
                    <span class="underline decoration-emerald-500/50 underline-offset-2">neobranding.cl</span>
                </a>
            </div>
        </div>
    </footer>
    @endif

    <!-- Theme Toggle JavaScript Logic -->
    <script>
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('motacast_theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('motacast_theme', 'dark');
                }
            });
        }

        // Global Collapsible Summary Toggle
        function toggleSummary(btn) {
            const container = btn.closest('.summary-container');
            if (!container) return;
            const textEl = container.querySelector('.summary-text');
            const label = btn.querySelector('.btn-label');
            const chevron = btn.querySelector('.chevron');
            const isExpanded = container.getAttribute('data-expanded') === 'true';

            if (isExpanded) {
                textEl.classList.add('line-clamp-2', 'line-clamp-3');
                container.setAttribute('data-expanded', 'false');
                if (label) label.textContent = 'Ver más';
                if (chevron) chevron.classList.remove('rotate-180');
            } else {
                textEl.classList.remove('line-clamp-2', 'line-clamp-3');
                container.setAttribute('data-expanded', 'true');
                if (label) label.textContent = 'Ver menos';
                if (chevron) chevron.classList.add('rotate-180');
            }
        }
    </script>

    <!-- High-Performance Ambient Dynamic Mesh & Particles Engine -->
    <script>
        (function() {
            const canvas = document.getElementById('ambientCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let width, height;
            let particles = [];
            let animationId = null;

            function isDarkMode() {
                return document.documentElement.classList.contains('dark');
            }

            function resize() {
                width = canvas.width = window.innerWidth;
                height = canvas.height = window.innerHeight;
                initParticles();
            }

            function initParticles() {
                const count = window.innerWidth < 768 ? 24 : 45;
                particles = [];
                for (let i = 0; i < count; i++) {
                    particles.push({
                        x: Math.random() * width,
                        y: Math.random() * height,
                        vx: (Math.random() - 0.5) * 0.4,
                        vy: (Math.random() - 0.5) * 0.4,
                        radius: Math.random() * 2 + 1.2,
                        type: Math.random() > 0.5 ? 1 : 2
                    });
                }
            }

            function draw() {
                ctx.clearRect(0, 0, width, height);
                const dark = isDarkMode();

                const color1 = dark ? '#00f0ff' : '#0284c7';
                const color2 = dark ? '#00ff87' : '#059669';
                const lineColor = dark ? 'rgba(0, 240, 255, 0.08)' : 'rgba(2, 132, 199, 0.09)';

                // Draw connecting filaments
                for (let i = 0; i < particles.length; i++) {
                    for (let j = i + 1; j < particles.length; j++) {
                        const dx = particles[i].x - particles[j].x;
                        const dy = particles[i].y - particles[j].y;
                        const dist = Math.sqrt(dx * dx + dy * dy);
                        if (dist < 130) {
                            ctx.beginPath();
                            ctx.moveTo(particles[i].x, particles[i].y);
                            ctx.lineTo(particles[j].x, particles[j].y);
                            ctx.strokeStyle = lineColor;
                            ctx.lineWidth = 0.8;
                            ctx.stroke();
                        }
                    }
                }

                // Draw floating particles
                for (let p of particles) {
                    p.x += p.vx;
                    p.y += p.vy;

                    if (p.x < 0) p.x = width;
                    else if (p.x > width) p.x = 0;
                    if (p.y < 0) p.y = height;
                    else if (p.y > height) p.y = 0;

                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                    ctx.fillStyle = p.type === 1 ? color1 : color2;
                    if (dark) {
                        ctx.shadowBlur = 6;
                        ctx.shadowColor = p.type === 1 ? color1 : color2;
                    }
                    ctx.fill();
                    ctx.shadowBlur = 0;
                }

                animationId = requestAnimationFrame(draw);
            }

            window.addEventListener('resize', resize);
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    cancelAnimationFrame(animationId);
                } else {
                    animationId = requestAnimationFrame(draw);
                }
            });

            resize();
            animationId = requestAnimationFrame(draw);
        })();

        // Reactive Mobile Dock Audio Controller
        window.handleDockAudioClick = function(e) {
            if (e) e.preventDefault();
            const soundwave = document.getElementById('dockSoundwave');
            const btn = document.getElementById('mobileAudioDockBtn');
            const audio = window.audioEngine || document.querySelector('audio');

            if (audio && audio.src && !audio.src.endsWith('#') && audio.src !== window.location.href) {
                if (audio.paused) {
                    audio.play().then(() => {
                        if (soundwave) soundwave.classList.add('is-playing');
                        if (btn) btn.classList.add('text-[#00c965]', 'dark:text-[#00ff87]');
                    }).catch(err => console.log('Dock play prevented:', err));
                } else {
                    audio.pause();
                    if (soundwave) soundwave.classList.remove('is-playing');
                    if (btn) btn.classList.remove('text-[#00c965]', 'dark:text-[#00ff87]');
                }
                return;
            }

            // If on books.show, start playing first ready chapter
            if (typeof togglePlay === 'function') {
                togglePlay();
                return;
            }

            // If on library, try first play action
            const firstPlay = document.querySelector('[data-play-book], .play-chapter-btn');
            if (firstPlay) {
                firstPlay.click();
                return;
            }

            // Fallback notification
            alert('No hay audio en reproducción activa. Selecciona un audiolibro para escuchar.');
        };

        // Attach listeners to any audio engine created on the page
        document.addEventListener('DOMContentLoaded', () => {
            const checkAndAttach = () => {
                const audio = window.audioEngine || document.querySelector('audio');
                if (audio && !audio._dockAttached) {
                    audio._dockAttached = true;
                    const soundwave = document.getElementById('dockSoundwave');
                    const btn = document.getElementById('mobileAudioDockBtn');
                    const updateState = () => {
                        const isPlaying = !audio.paused && !audio.ended && audio.currentTime > 0;
                        if (soundwave) soundwave.classList.toggle('is-playing', isPlaying);
                        if (btn) {
                            btn.classList.toggle('text-[#00c965]', isPlaying);
                            btn.classList.toggle('dark:text-[#00ff87]', isPlaying);
                        }
                    };
                    audio.addEventListener('play', updateState);
                    audio.addEventListener('playing', updateState);
                    audio.addEventListener('pause', updateState);
                    audio.addEventListener('ended', updateState);
                }
            };
            checkAndAttach();
            setInterval(checkAndAttach, 1000);
        });
    </script>

    <!-- Global Support / Feedback / Admin Note Modal -->
    <div id="supportTicketModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="card-tactile w-full max-w-lg rounded-3xl p-6 sm:p-7 space-y-4 relative shadow-2xl">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-cyan-950/60">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/20">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-cyan-200 tracking-tight">Contactar al Administrador</h3>
                        <p class="text-[11px] text-slate-500 dark:text-sky-400">Consulta, reporte de fallo, sugerencia o solicitud de cuota</p>
                    </div>
                </div>
                <button type="button" onclick="closeSupportModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form action="{{ route('tickets.store') }}" method="POST" class="space-y-3.5">
                @csrf

                <!-- Category -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase">Tipo de Mensaje</label>
                    <select name="type" required class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/60 rounded-xl text-slate-900 dark:text-cyan-100">
                        <option value="consulta">💬 Consulta General</option>
                        <option value="extension_limite">⚡ Solicitud de Ampliación de Cuota</option>
                        <option value="bug">🐛 Reporte de Fallo / Bug</option>
                        <option value="sugerencia">💡 Sugerencia para Fase Beta</option>
                    </select>
                </div>

                @guest
                    <!-- Guest Email Input -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase">Tu Correo Electrónico</label>
                        <input type="email" name="guest_email" required placeholder="correo@ejemplo.com" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/60 rounded-xl text-slate-900 dark:text-cyan-100 placeholder-slate-400">
                    </div>
                @endguest

                <!-- Subject -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase">Asunto Breve</label>
                    <input type="text" name="subject" required placeholder="Ej. Solicitud de cuota para tesis / Error al procesar audio" class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/60 rounded-xl text-slate-900 dark:text-cyan-100 placeholder-slate-400">
                </div>

                <!-- Message -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase">Detalle del Mensaje</label>
                    <textarea name="message" rows="3" required placeholder="Cuéntanos con detalle lo que necesitas o experimentaste..." class="w-full px-3 py-2 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/60 rounded-xl text-slate-900 dark:text-cyan-100 placeholder-slate-400"></textarea>
                </div>

                <!-- Beta Notice -->
                <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-[11px] text-amber-800 dark:text-amber-200/90 leading-relaxed">
                    <strong>Fase Early Access:</strong> MotaCastAudio está en pruebas activas. Cada mensaje llega directamente a la bandeja del Administrador para su seguimiento inmediato.
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeSupportModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-neon-tactile px-5 py-2.5 rounded-xl text-xs font-black shadow-md transition">
                        Enviar al Administrador
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openSupportModal() {
            document.getElementById('supportTicketModal').classList.remove('hidden');
        }
        function closeSupportModal() {
            document.getElementById('supportTicketModal').classList.add('hidden');
        }
    </script>

    @stack('scripts')
</body>
</html>

