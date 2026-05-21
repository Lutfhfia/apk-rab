<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsManku
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pengecekan apakah yang login adalah manajer_keuangan
        if (Auth::user()->role !== 'manajer_keuangan') {
            abort(403, 'Akses Ditolak. Hanya Manajer Keuangan yang bisa mengakses halaman ini.');
        }

        return $next($request);
    }
}
