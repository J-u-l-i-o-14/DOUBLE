<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectBasedOnRole
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Si l'utilisateur tente d'accéder au dashboard
            if ($request->is('dashboard') && $user->role === 'client') {
                return redirect('/blood-reservation');
            }
            
            // Si l'utilisateur tente d'accéder à blood-reservation
            if ($request->is('blood-reservation') && in_array($user->role, ['admin', 'gestionnaire'])) {
                return redirect('/dashboard');
            }
        }

        return $next($request);
    }
}
