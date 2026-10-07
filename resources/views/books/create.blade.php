@extends('layouts.app')

@section('title', 'Convertir Documento a Audiolibro - MotaCastAudio')
@section('meta_description', 'Convierte PDFs, Word (DOCX), texto directo e imágenes (OCR) en audiolibros interactivos con voces neuronales ultranaturales y resúmenes ejecutivos.')
@section('og_title', 'MotaCastAudio - Documentos a Audiolibros con IA')
@section('og_description', 'Sube tus libros, documentos o imágenes y escúchalos al instante con voces neuronales de alta fidelidad, navegación por capítulos y reproductor interactivo.')
@section('og_type', 'website')
@section('og_url', route('books.create'))
@section('og_image', asset('images/motacast-og-banner.jpg'))
@section('og_image_alt', 'MotaCastAudio - Conversión Inteligente a Audiolibros')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Back link -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ Auth::check() ? route('books.index') : route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-sky-400 hover:text-emerald-500 dark:hover:text-cyan-300 transition mb-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                <span>{{ Auth::check() ? 'Volver a Mis Documentos' : 'Inicio' }}</span>
            </a>
            <h1 class="text-2xl font-black text-slate-900 dark:text-cyan-200 tracking-tight">Agregar Documento o Libro</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-sky-300/80 mt-1">
                Convierte tus documentos Word, PDFs o textos en audiolibros con voces neuronales naturales de alta calidad.
            </p>
        </div>
    </div>

    <!-- Guía Visual para Usuarios (Onboarding Simple en 3 Pasos) -->
    <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-teal-500/10 via-emerald-500/10 to-cyan-500/10 border border-teal-500/30 text-slate-800 dark:text-cyan-100 shadow-sm">
        <div class="flex items-center gap-2 mb-3">
            <span class="text-base">🎧</span>
            <h2 class="text-xs sm:text-sm font-black uppercase tracking-wider text-teal-900 dark:text-[#00ff87]">
                ¿Cómo usar MotaCastAudio? — Fácil en 3 Pasos
            </h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-white/70 dark:bg-[#071014]/70 border border-slate-200/80 dark:border-cyan-900/50 shadow-xs">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-950 font-black flex items-center justify-center text-xs shrink-0">1</span>
                <div>
                    <strong class="text-slate-900 dark:text-cyan-200 block text-xs">Elige tu archivo o audio</strong>
                    <span class="text-[11px] text-slate-600 dark:text-sky-300/80 leading-snug block mt-0.5">Sube Word (.doc/.docx), PDF, fotos (OCR), audios (MP3, WAV) o pega texto directo.</span>
                </div>
            </div>
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-white/70 dark:bg-[#071014]/70 border border-slate-200/80 dark:border-cyan-900/50 shadow-xs">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-950 font-black flex items-center justify-center text-xs shrink-0">2</span>
                <div>
                    <strong class="text-slate-900 dark:text-cyan-200 block text-xs">Configura según el formato</strong>
                    <span class="text-[11px] text-slate-600 dark:text-sky-300/80 leading-snug block mt-0.5">Si es texto, prueba y elige la voz deseada. Si es audio, activa la transcripción a texto (STT).</span>
                </div>
            </div>
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-white/70 dark:bg-[#071014]/70 border border-slate-200/80 dark:border-cyan-900/50 shadow-xs">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-950 font-black flex items-center justify-center text-xs shrink-0">3</span>
                <div>
                    <strong class="text-slate-900 dark:text-cyan-200 block text-xs">Escucha y exporta al instante</strong>
                    <span class="text-[11px] text-slate-600 dark:text-sky-300/80 leading-snug block mt-0.5">Tu audiolibro comenzará al instante. Podrás escucharlo o descargarlo en MP3, TXT o Markdown.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- User Quota or Guest Mode Notice -->
    @auth
        @if (!Auth::user()->isAdmin())
            <div class="p-3.5 rounded-xl bg-slate-100 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/50 text-xs flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#00ff87]"></span>
                    <span class="text-slate-700 dark:text-sky-300">
                        Cuota Beta: <strong>{{ Auth::user()->books()->count() }}</strong> de {{ Auth::user()->book_limit === -1 ? 'Ilimitados' : Auth::user()->book_limit }} libros utilizados.
                    </span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="font-bold text-[#00c965] dark:text-[#00ff87]">
                        {{ Auth::user()->remainingBooks() === -1 ? 'Sin límite' : Auth::user()->remainingBooks() . ' restantes' }}
                    </span>
                    <button type="button" onclick="openSupportModal()" class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hover:underline">
                        ¿Necesitas más?
                    </button>
                </div>
            </div>
        @endif
    @else
        <div class="p-3.5 rounded-xl bg-cyan-50 dark:bg-cyan-950/40 border border-cyan-400/40 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                <span class="text-cyan-900 dark:text-cyan-200">
                    <strong>Modo Prueba Gratuita:</strong> Tienes 1 conversión gratuita disponible sin registro previo.
                </span>
            </div>
            <a href="{{ route('register') }}" class="font-bold text-cyan-700 dark:text-cyan-300 underline hover:text-cyan-500">Crear Cuenta</a>
        </div>
    @endauth

    <!-- Upload Form (Tactile 3D in Day Mode, Cyber Glow in Dark Mode) -->
    <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data" class="card-tactile rounded-2xl p-6 md:p-8 space-y-6" id="uploadForm">
        @csrf

        <!-- Intake Mode Tabs: File Upload vs Direct Text Paste -->
        @include('books.partials.create-intake-tabs')

        <!-- Metadata Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="title" class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider mb-1.5">
                    Título (Opcional)
                </label>
                <input 
                    type="text" 
                    name="title" 
                    id="title" 
                    value="{{ old('title') }}" 
                    placeholder="Se usará el nombre del documento si está vacío"
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/50 rounded-xl text-slate-900 dark:text-cyan-200 placeholder-slate-400 dark:placeholder-sky-500/40 focus:outline-none focus:ring-2 focus:ring-[#00ff87] transition"
                >
            </div>
            <div>
                <label for="author" class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider mb-1.5">
                    Autor / Origen (Opcional)
                </label>
                <input 
                    type="text" 
                    name="author" 
                    id="author" 
                    value="{{ old('author') }}" 
                    placeholder="Ej. Héctor Mota Zorrilla"
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 dark:bg-[#071014] border border-slate-300 dark:border-cyan-900/50 rounded-xl text-slate-900 dark:text-cyan-200 placeholder-slate-400 dark:placeholder-sky-500/40 focus:outline-none focus:ring-2 focus:ring-[#00ff87] transition"
                >
            </div>
        </div>

        <!-- Admin Only: Assign Book to specific user -->
        @auth
            @if (Auth::user()->isAdmin() && isset($registeredUsers) && $registeredUsers->count() > 0)
                <div class="p-3.5 rounded-xl bg-teal-50/50 dark:bg-[#071014] border border-teal-500/30">
                    <label for="assigned_user_id" class="block text-xs font-bold text-teal-800 dark:text-cyan-300 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Asignar este libro a un usuario específico (Privilegio Admin):</span>
                    </label>
                    <select 
                        name="assigned_user_id" 
                        id="assigned_user_id"
                        class="w-full px-3 py-2 text-xs bg-white dark:bg-[#0d1c22] border border-teal-300 dark:border-cyan-800/60 rounded-xl text-slate-900 dark:text-cyan-200 focus:ring-2 focus:ring-[#00ff87] outline-none font-medium"
                    >
                        <option value="{{ Auth::id() }}">Mí mismo ({{ Auth::user()->name }})</option>
                        @foreach ($registeredUsers as $u)
                            @if ($u->id !== Auth::id())
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            @endif
        @endauth

        <!-- Step 2A: Voice & Reading Preferences (for Documents / Text / OCR) -->
        <div id="stepVoiceSection" class="transition-all duration-300">
            @include('books.partials.create-voice-preferences')
        </div>

        <!-- Step 2B: Audio & STT Preferences (dynamically shown when audio is selected) -->
        <div id="stepAudioSection" class="hidden transition-all duration-300">
            @include('books.partials.create-audio-preferences')
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button 
                type="submit" 
                id="submitBtn"
                class="w-full py-3.5 px-6 btn-neon-tactile rounded-xl text-sm font-black transition flex items-center justify-center gap-2"
            >
                <svg class="w-5 h-5 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
                </svg>
                <span id="submitBtnText">Comenzar Extracción y Síntesis</span>
            </button>
        </div>

    </form>

    <!-- Modals (Overlay, Text Limit, Tactile Notice) -->
    @include('books.partials.create-modals')

</div>
@endsection

@push('scripts')
    @include('books.partials.create-scripts')
@endpush
