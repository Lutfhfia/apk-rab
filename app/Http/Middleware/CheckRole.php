<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Usage: CheckRole::class . ':admin_keuangan,manajer_keuangan'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $userRole = $user->role instanceof \App\Enums\UserRole
            ? $user->role->normalizedValue()
            : $user->getRawOriginal('role');

        if ($userRole === 'manajer_operasional') {
            $userRole = 'manajer_keuangan';
        }

        if (!in_array($userRole, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
