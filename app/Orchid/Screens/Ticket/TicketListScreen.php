<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Ticket;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class TicketListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'metrics' => [
                'total' => ['value' => number_format(SupportTicket::count())],
                'pending' => ['value' => number_format(SupportTicket::pending()->count())],
                'extensions' => ['value' => number_format(SupportTicket::extensions()->where('status', 'pendiente')->count())],
                'resolved' => ['value' => number_format(SupportTicket::where('status', 'resuelto')->count())],
            ],
            'tickets' => SupportTicket::with('user')
                ->orderByDesc('id')
                ->paginate(15),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Mesa de Ayuda & Cuotas';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Gestión de solicitudes de ampliación de cuota, reportes técnicos y feedback de usuarios en MotaCast.';
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [];
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
                'Total Solicitudes' => 'metrics.total',
                'Pendientes' => 'metrics.pending',
                'Ampliaciones Cuota' => 'metrics.extensions',
                'Resueltos' => 'metrics.resolved',
            ]),

            Layout::table('tickets', [
                TD::make('id', 'ID')->width('70px'),

                TD::make('requester', 'Remitente')
                    ->render(function (SupportTicket $ticket) {
                        if ($ticket->user) {
                            return "<strong>{$ticket->user->name}</strong><br><small class='text-muted'>{$ticket->user->email}</small>";
                        }

                        return "<span>Invitado</span><br><small class='text-muted'>{$ticket->guest_email}</small>";
                    }),

                TD::make('type', 'Tipo')
                    ->render(fn (SupportTicket $ticket) => "<span class='badge bg-info text-dark'>{$ticket->formatted_type}</span>"),

                TD::make('subject', 'Asunto / Mensaje')
                    ->render(function (SupportTicket $ticket) {
                        $subj = e($ticket->subject);
                        $msg = e($ticket->message);
                        $requested = $ticket->requested_books > 0 ? " <span class='badge bg-success'>+{$ticket->requested_books} libros</span>" : '';

                        return "<div><strong>{$subj}</strong>{$requested}<br><small class='text-muted'>{$msg}</small></div>";
                    }),

                TD::make('status', 'Estado')
                    ->render(function (SupportTicket $ticket) {
                        $badge = match ($ticket->status) {
                            'resuelto' => 'bg-success',
                            'rechazado' => 'bg-danger',
                            default => 'bg-warning text-dark',
                        };

                        return "<span class='badge {$badge}'>{$ticket->status}</span>";
                    }),

                TD::make('created_at', 'Fecha')
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT),

                TD::make('Acciones')
                    ->align(TD::ALIGN_CENTER)
                    ->width('120px')
                    ->render(fn (SupportTicket $ticket) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Button::make('Aprobar Ampliación (+'.$ticket->requested_books.')')
                                ->icon('bs.check-circle')
                                ->canSee($ticket->type === 'extension_limite' && $ticket->isPending())
                                ->method('approveExtension', ['id' => $ticket->id]),

                            Button::make('Marcar como Resuelto')
                                ->icon('bs.check2-all')
                                ->canSee($ticket->isPending())
                                ->method('resolveTicket', ['id' => $ticket->id]),

                            Button::make('Eliminar Ticket')
                                ->icon('bs.trash3')
                                ->confirm('¿Deseas eliminar permanentemente este ticket?')
                                ->method('deleteTicket', ['id' => $ticket->id]),
                        ])),
            ])->title('Tickets Recientes'),
        ];
    }

    /**
     * Approve quota extension.
     */
    public function approveExtension(Request $request): void
    {
        $ticket = SupportTicket::findOrFail($request->get('id'));
        if ($ticket->user) {
            $ticket->user->grantCourtesyExtension($ticket->requested_books ?: 1);
        }

        $ticket->update([
            'status' => 'resuelto',
            'admin_reply' => 'Ampliación de cortesía concedida con éxito por administración.',
            'resolved_at' => now(),
        ]);

        Toast::info('Cuota ampliada y ticket marcado como resuelto.');
    }

    /**
     * Mark ticket as resolved.
     */
    public function resolveTicket(Request $request): void
    {
        $ticket = SupportTicket::findOrFail($request->get('id'));
        $ticket->update([
            'status' => 'resuelto',
            'admin_reply' => 'Atendido por administración.',
            'resolved_at' => now(),
        ]);

        Toast::info('Ticket resuelto.');
    }

    /**
     * Delete ticket.
     */
    public function deleteTicket(Request $request): void
    {
        $ticket = SupportTicket::findOrFail($request->get('id'));
        $ticket->delete();

        Toast::info('Ticket eliminado.');
    }
}
