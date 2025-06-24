<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('/login'); // Redirect ke halaman login jika belum login
        }

        $user = Auth::user();

        // Jika user memiliki peran yang diizinkan, lanjutkan request
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // Jika tidak memiliki peran yang diizinkan, tampilkan error 403
        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}