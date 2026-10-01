<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MotaCastAudio') - Mota Zorrilla</title>

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
                                <span class="hidden xs:inline-block px-1.5 py-0.5 rounded-md text-[9px] font-mono font-bold bg-slate-100 dark:bg-cyan-950/70 text-slate-600 dark:text-cyan-300 border border-slate-300/80 dark:border-cyan-800/40">v0.7.0</span>
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
                                <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ request()->routeIs('admin.users.*') ? 'text-teal-700 dark:text-cyan-300 bg-teal-500/10 dark:bg-cyan-950/40 border border-teal-500/30 dark:border-cyan-500/30' : 'text-slate-600 dark:text-sky-300 hover:bg-slate-100 dark:hover:bg-slate-800' }} transition inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <span>Usuarios</span>
                                </a>
                            @endif

                            <a href="{{ route('books.create') }}" class="btn-neon-tactile inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-black transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
    <nav class="sm:hidden fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-[#080d0f]/95 backdrop-blur-lg border-t border-slate-200 dark:border-cyan-950/60 z-40 px-3 py-2 flex items-center justify-around shadow-2xl">
        <!-- Tab 1: Mis Libros -->
        <a href="{{ route('books.index') }}" class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl {{ request()->routeIs('books.index') && !request()->routeIs('admin.*') ? 'text-[#00c965] dark:text-[#00ff87] font-bold' : 'text-slate-500 dark:text-sky-400' }} transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span class="text-[10px]">Libros</span>
        </a>

        <!-- Admin Tab if admin -->
        @if (Auth::user()->isAdmin())
            <a href="{{ route('admin.users.index') }}" class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl {{ request()->routeIs('admin.users.*') ? 'text-teal-600 dark:text-cyan-300 font-bold' : 'text-slate-500 dark:text-sky-400' }} transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span class="text-[10px]">Usuarios</span>
            </a>
        @endif

        <!-- Tab Central: + Agregar Documento (Destacado y Radiante 3D) -->
        <a href="{{ route('books.create') }}" class="flex flex-col items-center justify-center w-12 h-12 -mt-5 btn-neon-tactile rounded-2xl shadow-neon-md transition transform active:scale-95" title="Agregar nuevo documento">
            <svg class="w-6 h-6 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
        </a>

        <!-- Tab: Reproductor / Streaming -->
        <a href="{{ route('books.index') }}#player" class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl text-slate-500 dark:text-sky-400 transition" onclick="if(window.audioEngine && window.audioEngine.src){window.audioEngine.play();}">
            <div class="flex items-center gap-0.5 h-5">
                <span class="w-1 bg-[#00ff87] rounded-full bar-wave-1"></span>
                <span class="w-1 bg-[#00ff87] rounded-full bar-wave-2"></span>
                <span class="w-1 bg-[#00ff87] rounded-full bar-wave-3"></span>
                <span class="w-1 bg-[#00ff87] rounded-full bar-wave-4"></span>
            </div>
            <span class="text-[10px]">Audio</span>
        </a>
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
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-50 dark:bg-cyan-950/70 text-emerald-700 dark:text-[#00ff87] border border-emerald-200 dark:border-cyan-800/50 shadow-sm">v0.7.0</span>
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
    </script>

    @stack('scripts')
</body>
</html>

