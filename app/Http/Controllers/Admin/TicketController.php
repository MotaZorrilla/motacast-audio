<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = SupportTicket::with('user');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('guest_email', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $tickets = $query->orderByRaw("CASE WHEN status = 'pendiente' THEN 1 WHEN status = 'en_revision' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => SupportTicket::count(),
            'pending' => SupportTicket::where('status', 'pendiente')->count(),
            'extensions_pending' => SupportTicket::where('type', 'extension_limite')->where('status', 'pendiente')->count(),
            'resolved' => SupportTicket::where('status', 'resuelto')->count(),
        ];

        return view('admin.tickets', compact('tickets', 'stats'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'status' => 'required|in:pendiente,en_revision,resuelto,rechazado',
            'admin_reply' => 'required|string|max:2000',
        ]);

        $ticket->update([
            'status' => $validated['status'],
            'admin_reply' => $validated['admin_reply'],
            'resolved_at' => in_array($validated['status'], ['resuelto', 'rechazado']) ? now() : null,
        ]);

        return back()->with('success', "Ticket #{$ticket->id} actualizado correctamente.");
    }

    public function approveExtension(Request $request, SupportTicket $ticket)
    {
        $this->authorizeAdmin();

        $additional = (int) $request->input('books', $ticket->requested_books ?: 2);
        if ($additional < 1) {
            $additional = 2;
        }

        $user = $ticket->user;
        if ($user) {
            if ($user->book_limit !== -1) {
                $user->book_limit += $additional;
                $user->save();
            }

            $ticket->update([
                'status' => 'resuelto',
                'admin_reply' => "Solicitud aprobada: se añadieron {$additional} libros a tu cuenta. ¡Gracias por participar en la fase Beta!",
                'resolved_at' => now(),
            ]);

            return back()->with('success', "¡Extensión de +{$additional} libros concedida al usuario {$user->name}! Nuevo límite: {$user->book_limit} libros.");
        }

        return back()->with('error', 'No se pudo asociar el ticket con un usuario registrado.');
    }

    public function destroy(SupportTicket $ticket)
    {
        $this->authorizeAdmin();
        $ticketId = $ticket->id;
        $ticket->delete();

        return back()->with('success', "Ticket #{$ticketId} eliminado del sistema.");
    }

    protected function authorizeAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Acceso denegado: Se requieren privilegios de Administrador.');
        }
    }
}
