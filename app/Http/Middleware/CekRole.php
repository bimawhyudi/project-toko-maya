<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekRole
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (in_array($user->role, $peran, true) || $user->role === 'pemilik') {
            return $next($request);
        }

        abort(403, 'Anda tidak punya hak akses ke halaman ini.');
    }
}