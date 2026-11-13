<!-- Audio & STT Preferences (Displayed dynamically when an audio file is selected - Kairos Styling) -->
<div class="border-t border-slate-200 dark:border-white/[0.08] pt-6 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-[11px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest flex items-center gap-2">
            <span class="text-base">🎙️</span>
            <span>Opciones de Archivo de Audio y Transcripción</span>
        </h3>
        <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-[#5b4f96]/10 border border-[#5b4f96]/20 text-[#5b4f96] dark:text-[#a59be0]">
            Detección Inteligente (STT)
        </span>
    </div>

    <!-- Audio File Info Card -->
    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 dark:bg-[#121619] border border-slate-200 dark:border-white/[0.08] space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#5b4f96]/10 border border-[#5b4f96]/20 text-[#5b4f96] dark:text-[#a59be0] flex items-center justify-center text-lg shrink-0">
                    🎵
                </div>
                <div>
                    <strong id="audioFileNameDisplay" class="text-slate-900 dark:text-white block text-xs sm:text-sm font-bold truncate max-w-xs sm:max-w-md">grabacion.mp3</strong>
                    <span id="audioFileSizeDisplay" class="text-[11px] font-mono text-slate-500 dark:text-slate-400">Audio listo para procesar</span>
                </div>
            </div>

            <!-- Instant STT Action Button -->
            <button 
                type="button" 
                id="btnTranscribeAudioNow"
                onclick="triggerDirectSttFromSelectedAudio()"
                class="px-4 py-2.5 rounded-xl text-xs font-bold btn-kairos-primary flex items-center justify-center gap-1.5 shrink-0"
            >
                <span>🎙️ Transcribir a Texto Ahora</span>
            </button>
        </div>

        <!-- STT Progress Box -->
        <div id="directSttStatusBox" class="hidden p-3 rounded-xl bg-[#5b4f96]/10 border border-[#5b4f96]/20 text-[#5b4f96] dark:text-[#a59be0] text-xs font-semibold items-center gap-2">
            <svg class="w-4 h-4 animate-spin text-[#5b4f96] shrink-0" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span id="directSttStatusText">Transcribiendo voz a texto con IA...</span>
        </div>
    </div>

    <!-- Mode Selector for Audio File -->
    <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#121619] border border-slate-200 dark:border-white/[0.08] text-xs space-y-2">
        <p class="text-slate-700 dark:text-slate-300 font-semibold flex items-center gap-1.5">
            <span>💡</span>
            <span>¿Qué deseas hacer con esta grabación?</span>
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
            <label class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#151a1e] cursor-pointer hover:border-[#5b4f96] transition">
                <input type="radio" name="audio_action_mode" value="stt_only" checked onchange="onAudioActionModeChanged(this.value)" class="mt-0.5 text-[#5b4f96] focus:ring-[#5b4f96]">
                <div>
                    <strong class="text-slate-900 dark:text-white block text-xs font-bold">Solo Transcribir a Texto (STT)</strong>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block leading-tight mt-0.5">Obtén el texto transcrito de inmediato para leer, editar o exportar en TXT/Markdown.</span>
                </div>
            </label>
            <label class="flex items-start gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#151a1e] cursor-pointer hover:border-[#5b4f96] transition">
                <input type="radio" name="audio_action_mode" value="stt_and_tts" onchange="onAudioActionModeChanged(this.value)" class="mt-0.5 text-[#5b4f96] focus:ring-[#5b4f96]">
                <div>
                    <strong class="text-slate-900 dark:text-white block text-xs font-bold">Transcribir y Crear Audiolibro</strong>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block leading-tight mt-0.5">Sintetiza la transcripción con una nueva voz neuronal y genera capítulos estructurados.</span>
                </div>
            </label>
        </div>
    </div>

    <!-- Optional Voice Settings when "stt_and_tts" is selected -->
    <div id="audioTtsVoiceContainer" class="hidden p-4 rounded-xl bg-slate-50 dark:bg-[#121619] border border-slate-200 dark:border-white/[0.08] space-y-3">
        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
            Voz para el nuevo Audiolibro
        </label>
        <p class="text-[11px] text-slate-500 dark:text-slate-400">
            El audio será transcrito a texto y luego narrado con la voz que elijas aquí:
        </p>
        <select 
            id="audioAltVoiceSelect"
            onchange="syncAudioVoice(this.value)"
            class="w-full px-3.5 py-2.5 text-xs sm:text-sm input-kairos rounded-xl font-medium"
        >
            @foreach ($voices as $v)
                <option value="{{ $v['id'] }}" {{ $v['recommended'] ? 'selected' : '' }}>
                    {{ $v['name'] }}
                </option>
            @endforeach
        </select>
    </div>
</div>
