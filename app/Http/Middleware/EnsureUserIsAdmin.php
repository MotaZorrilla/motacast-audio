<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request restricted to administrators.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Acceso denegado: Se requieren privilegios de Administrador.',
                ], 403);
            }

            abort(403, 'Acceso denegado: Se requieren privilegios de Administrador.');
        }

        return $next($request);
    }
}
