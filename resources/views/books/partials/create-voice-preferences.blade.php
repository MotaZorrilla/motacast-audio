<!-- Voice & Reading Preferences (Kairos Styling) -->
<div class="border-t border-slate-200 dark:border-white/[0.08] pt-6 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-[11px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest flex items-center gap-2">
            <svg class="w-4 h-4 text-[#5b4f96] dark:text-[#a59be0]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z" />
            </svg>
            <span>Configuración de Voz y Lectura</span>
        </h3>
        <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-[#5b4f96]/10 border border-[#5b4f96]/20 text-[#5b4f96] dark:text-[#a59be0]">
            Neural Edge Audio
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Voice Selector -->
        <div class="md:col-span-1">
            <div class="flex items-center justify-between mb-1.5">
                <label for="voice" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                    Voz Narradora
                </label>
                <button 
                    type="button" 
                    id="btnVoicePreview"
                    onclick="toggleVoicePreview()"
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-bold text-[#5b4f96] dark:text-[#a59be0] bg-[#5b4f96]/10 hover:bg-[#5b4f96]/20 border border-[#5b4f96]/25 transition"
                    title="Escuchar una muestra de esta voz"
                >
                    <span id="previewVoiceIcon">🔊</span>
                    <span id="previewVoiceText">Muestra</span>
                </button>
            </div>
            <select 
                name="voice" 
                id="voice" 
                onchange="onVoiceChanged()"
                class="w-full px-3.5 py-2.5 text-xs sm:text-sm input-kairos rounded-xl font-medium"
            >
                @foreach ($voices as $v)
                    <option value="{{ $v['id'] }}" {{ $v['recommended'] ? 'selected' : '' }}>
                        {{ $v['name'] }}
                    </option>
                @endforeach
            </select>
            <!-- Floating Voice Preview Status Badge -->
            <div id="voicePreviewPlayerContainer" class="hidden mt-2 flex items-center gap-2 p-2 rounded-xl bg-[#5b4f96]/10 border border-[#5b4f96]/20 text-xs text-[#5b4f96] dark:text-[#a59be0]">
                <span class="inline-block w-2 h-2 rounded-full bg-[#5b4f96] animate-ping shrink-0"></span>
                <span id="voicePreviewStatus" class="font-medium truncate">Reproduciendo muestra de voz...</span>
            </div>
        </div>

        <!-- Speed / Rate -->
        <div>
            <label for="speed_rate" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                Velocidad
            </label>
            <select 
                name="speed_rate" 
                id="speed_rate" 
                onchange="onVariantChanged()"
                class="w-full px-3.5 py-2.5 text-xs sm:text-sm input-kairos rounded-xl font-medium"
            >
                <option value="-20%">Lenta (-20%)</option>
                <option value="-10%">Pausada (-10%)</option>
                <option value="+0%" selected>Natural (Recomendada)</option>
                <option value="+10%">Ágil (+10%)</option>
                <option value="+20%">Rápida (+20%)</option>
                <option value="+30%">Ultra Rápida (+30%)</option>
            </select>
        </div>

        <!-- Pitch -->
        <div>
            <label for="pitch" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                Tono
            </label>
            <select 
                name="pitch" 
                id="pitch" 
                onchange="onVariantChanged()"
                class="w-full px-3.5 py-2.5 text-xs sm:text-sm input-kairos rounded-xl font-medium"
            >
                <option value="-20Hz">Grave (-20Hz)</option>
                <option value="-10Hz">Profundo (-10Hz)</option>
                <option value="+0Hz" selected>Estándar (+0Hz)</option>
                <option value="+10Hz">Claro (+10Hz)</option>
                <option value="+20Hz">Agudo (+20Hz)</option>
            </select>
        </div>
    </div>

    <!-- Intelligent Masonic & Normalization Badge (Hidden by default; appears only when detected) -->
    <div id="masonicNoticeBox" class="hidden p-3.5 rounded-xl bg-slate-50 dark:bg-[#121619] border border-slate-200 dark:border-white/[0.08] flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs transition duration-300">
        <div class="flex items-center gap-2">
            <span class="text-sm shrink-0">🏛️</span>
            <div class="text-slate-700 dark:text-slate-300">
                <strong>Modo Simbólico & Masónico Detectado:</strong> Expansión fonética activa de fórmulas litúrgicas y abreviaturas ritualísticas (Q∴H∴ ➔ Querido Hermano, V∴M∴, GADU, etc.).
            </div>
        </div>
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shrink-0 self-start sm:self-auto">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Auto-Activado
        </span>
    </div>
</div>
