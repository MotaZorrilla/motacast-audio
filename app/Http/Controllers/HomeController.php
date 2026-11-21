<?php

namespace App\Http\Controllers;

use App\Services\GuestSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected GuestSessionService $guestSession
    ) {}

    /**
     * Entry route resolving authenticated library, landing page, or active guest trial flow cleanly (cacheable).
     */
    public function index(Request $request, BookController $bookController): View|RedirectResponse
    {
        if (Auth::check()) {
            return $bookController->index($request);
        }

        if ($request->has('reset') || $request->has('new') || $request->has('reset_trial')) {
            $this->guestSession->resetSession();

            return $bookController->create($request);
        }

        if ($activeBook = $this->guestSession->getActiveTrialBook()) {
            return redirect()->route('books.show', $activeBook->id);
        }

        if ($this->guestSession->isTrialExhausted()) {
            return redirect()->route('login')
                ->with('info', 'Has utilizado tu conversión de prueba gratuita. Inicia sesión o regístrate para acceder a tu biblioteca.');
        }

        return view('landing');
    }
}
