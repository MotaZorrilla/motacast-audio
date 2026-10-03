<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    /**
     * Submit a general ticket, note, bug report or feedback.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:consulta,bug,sugerencia,extension_limite',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2500',
            'guest_email' => Auth::check() ? 'nullable|email' : 'required|email|max:255',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'guest_email' => Auth::check() ? Auth::user()->email : $validated['guest_email'],
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'pendiente',
        ]);

        return back()->with('success', 'Tu mensaje ha sido enviado al Administrador. ¡Muchas gracias por colaborar con la versión Beta!');
    }

    /**
     * Handle semi-automatic quota extension request.
     */
    public function requestExtension(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('register')
                ->with('info', 'Por favor regístrate o inicia sesión para solicitar una cuota ampliada.');
        }

        $user = Auth::user();

        // If user is already unlimited or admin
        if ($user->isAdmin() || $user->book_limit === -1) {
            return back()->with('info', 'Tu cuenta ya cuenta con capacidad ilimitada de documentos.');
        }

        // Branch 1: Automatic 1-time courtesy bump (+1 book)
        if ($user->canRequestAutoExtension()) {
            $user->grantCourtesyExtension(1);

            SupportTicket::create([
                'user_id' => $user->id,
                'type' => 'extension_limite',
                'subject' => 'Extensión automática de cortesía (+1 audiolibro)',
                'message' => 'El usuario solicitó una extensión y el sistema Beta le otorgó +1 libro de cortesía automáticamente.',
                'status' => 'resuelto',
                'requested_books' => 1,
                'admin_reply' => 'Concedida automáticamente por el protocolo de Early Access.',
                'resolved_at' => now(),
            ]);

            return back()->with('success', "¡Extensión de cortesía aprobada al instante! Ahora puedes cargar hasta {$user->book_limit} libros en tu cuenta.");
        }

        // Branch 2: User already used courtesy bump, check for pending request
        $existingPending = SupportTicket::where('user_id', $user->id)
            ->where('type', 'extension_limite')
            ->where('status', 'pendiente')
            ->first();

        if ($existingPending) {
            return back()->with('info', 'Ya tienes una solicitud de ampliación pendiente en la bandeja del Administrador.');
        }

        // Branch 3: Register ticket for admin 1-click review
        $reason = $request->input('reason', 'Solicitud de ampliación de cuota tras agotar la cortesía inicial de fase Beta.');
        $requestedBooks = (int) $request->input('requested_books', 2);

        SupportTicket::create([
            'user_id' => $user->id,
            'guest_email' => $user->email,
            'type' => 'extension_limite',
            'subject' => "Solicitud de +{$requestedBooks} audiolibros ({$user->name})",
            'message' => $reason,
            'status' => 'pendiente',
            'requested_books' => $requestedBooks,
        ]);

        return back()->with('success', 'Tu solicitud de ampliación ha sido enviada al Administrador. Recibirás respuesta a la brevedad.');
    }
}
