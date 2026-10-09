<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Telemetry;

use App\Models\TrafficLog;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TelemetryScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $totalLogs = TrafficLog::count();
        $todayLogs = TrafficLog::today()->count();
        $realUsers = TrafficLog::realUsers()->count();
        $crawlers = TrafficLog::crawlers()->count();

        $countries = TrafficLog::select(
            'country_code',
            'country_name',
            'country_flag',
            DB::raw('count(*) as total')
        )
            ->groupBy('country_code', 'country_name', 'country_flag')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $recentLogs = TrafficLog::with('user')
            ->orderByDesc('id')
            ->paginate(15);

        return [
            'metrics' => [
                'total' => ['value' => number_format($totalLogs)],
                'today' => ['value' => number_format($todayLogs)],
                'real' => ['value' => number_format($realUsers)],
                'bots' => ['value' => number_format($crawlers)],
            ],
            'countries' => $countries,
            'recentLogs' => $recentLogs,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Telemetría de Tráfico Global';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Métricas en tiempo real de visitantes, países, dispositivos y rastreadores web en MotaCast.';
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Vaciar Registros')
                ->icon('bs.trash3')
                ->confirm('¿Estás seguro de que deseas eliminar permanentemente todo el historial de telemetría?')
                ->method('clearLogs'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Total Peticiones' => 'metrics.total',
                'Peticiones Hoy' => 'metrics.today',
                'Usuarios Reales' => 'metrics.real',
                'Bots / Rastreadores' => 'metrics.bots',
            ]),

            Layout::table('recentLogs', [
                TD::make('id', 'ID')->width('70px'),
                TD::make('country', 'País')->render(fn (TrafficLog $log) => "{$log->country_flag} {$log->country_code}"),
                TD::make('path', 'Ruta')->render(fn (TrafficLog $log) => "<code>{$log->path}</code>"),
                TD::make('action_details', 'Detalle')->render(fn (TrafficLog $log) => e($log->action_details ?: '-')),
                TD::make('device_type', 'Dispositivo')->render(fn (TrafficLog $log) => "<span class='badge bg-dark'>{$log->device_type}</span>"),
                TD::make('is_crawler', 'Tipo')->render(fn (TrafficLog $log) => $log->is_crawler ? '<span class="badge bg-warning text-dark">Bot</span>' : '<span class="badge bg-success">Humano</span>'),
                TD::make('created_at', 'Hora')->usingComponent(DateTimeSplit::class)->align(TD::ALIGN_RIGHT),
            ])->title('Últimas Peticiones Registradas'),
        ];
    }

    /**
     * Clear all traffic logs.
     */
    public function clearLogs(): void
    {
        TrafficLog::truncate();
        Toast::info('Todos los registros de telemetría han sido vaciados.');
    }
}
