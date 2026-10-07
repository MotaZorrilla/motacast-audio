<!-- Audio & STT Preferences (Displayed dynamically when an audio file is selected) -->
<div class="border-t border-slate-200 dark:border-cyan-950/60 pt-5 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold text-slate-900 dark:text-cyan-200 uppercase tracking-wider flex items-center gap-2">
            <span class="text-base">🎙️</span>
            <span>Opciones de Archivo de Audio y Transcripción</span>
        </h3>
        <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-[#00ff87]/20 border border-[#00ff87]/40 text-[#00c965] dark:text-[#00ff87]">
            Detección Inteligente (STT)
        </span>
    </div>

    <!-- Audio File Info Card -->
    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#071014] border border-slate-200 dark:border-cyan-900/60 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-[#00ff87] flex items-center justify-center text-lg shrink-0">
                    🎵
                </div>
                <div>
                    <strong id="audioFileNameDisplay" class="text-slate-900 dark:text-cyan-100 block text-xs sm:text-sm font-bold truncate max-w-xs sm:max-w-md">grabacion.mp3</strong>
                    <span id="audioFileSizeDisplay" class="text-[11px] font-mono text-slate-500 dark:text-sky-400">Audio listo para procesar</span>
                </div>
            </div>

            <!-- Instant STT Action Button -->
            <button 
                type="button" 
                id="btnTranscribeAudioNow"
                onclick="triggerDirectSttFromSelectedAudio()"
                class="px-4 py-2 rounded-xl text-xs font-bold btn-neon-tactile text-slate-950 flex items-center justify-center gap-1.5 shrink-0 transition"
            >
                <span>🎙️ Transcribir a Texto Ahora</span>
            </button>
        </div>

        <!-- STT Progress Box -->
        <div id="directSttStatusBox" class="hidden p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-800 dark:text-[#00ff87] text-xs font-semibold items-center gap-2">
            <svg class="w-4 h-4 animate-spin text-[#00ff87] shrink-0" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span id="directSttStatusText">Transcribiendo voz a texto con IA...</span>
        </div>
    </div>

    <!-- Mode Selector for Audio File -->
    <div class="p-3.5 rounded-xl bg-cyan-500/5 border border-cyan-500/20 text-xs space-y-2">
        <p class="text-slate-700 dark:text-cyan-200 font-semibold flex items-center gap-1.5">
            <span>💡</span>
            <span>¿Qué deseas hacer con esta grabación?</span>
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
            <label class="flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-200 dark:border-cyan-900/50 bg-white/80 dark:bg-[#0d1c22] cursor-pointer hover:border-[#00ff87] transition">
                <input type="radio" name="audio_action_mode" value="stt_only" checked onchange="onAudioActionModeChanged(this.value)" class="mt-0.5 text-emerald-500 focus:ring-[#00ff87]">
                <div>
                    <strong class="text-slate-900 dark:text-cyan-100 block text-xs">Solo Transcribir a Texto (STT)</strong>
                    <span class="text-[11px] text-slate-500 dark:text-sky-400 block leading-tight">Obtén el texto transcrito de inmediato para leer, editar o exportar en TXT/Markdown.</span>
                </div>
            </label>
            <label class="flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-200 dark:border-cyan-900/50 bg-white/80 dark:bg-[#0d1c22] cursor-pointer hover:border-[#00ff87] transition">
                <input type="radio" name="audio_action_mode" value="stt_and_tts" onchange="onAudioActionModeChanged(this.value)" class="mt-0.5 text-emerald-500 focus:ring-[#00ff87]">
                <div>
                    <strong class="text-slate-900 dark:text-cyan-100 block text-xs">Transcribir y Crear Audiolibro</strong>
                    <span class="text-[11px] text-slate-500 dark:text-sky-400 block leading-tight">Sintetiza la transcripción con una nueva voz neuronal y genera capítulos estructurados.</span>
                </div>
            </label>
        </div>
    </div>

    <!-- Optional Voice Settings when "stt_and_tts" is selected -->
    <div id="audioTtsVoiceContainer" class="hidden p-3.5 rounded-xl bg-slate-100/70 dark:bg-[#071014] border border-slate-200 dark:border-cyan-900/50 space-y-3">
        <label class="block text-xs font-bold text-slate-700 dark:text-cyan-300 uppercase tracking-wider">
            Voz para el nuevo Audiolibro
        </label>
        <p class="text-[11px] text-slate-500 dark:text-sky-400">
            El audio será transcrito a texto y luego narrado con la voz que elijas aquí:
        </p>
        <select 
            id="audioAltVoiceSelect"
            onchange="syncAudioVoice(this.value)"
            class="w-full px-3 py-2 text-xs sm:text-sm bg-white dark:bg-[#0d1c22] border border-slate-300 dark:border-cyan-900/50 rounded-xl text-slate-900 dark:text-cyan-200 focus:outline-none focus:ring-2 focus:ring-[#00ff87]"
        >
            @foreach ($voices as $v)
                <option value="{{ $v['id'] }}" {{ $v['recommended'] ? 'selected' : '' }}>
                    {{ $v['name'] }}
                </option>
            @endforeach
        </select>
    </div>
</div>
