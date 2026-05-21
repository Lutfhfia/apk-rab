<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsDirut
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pengecekan apakah yang login adalah direktur_utama
        if (Auth::user()->role !== 'direktur_utama') {
            abort(403, 'Akses Ditolak. Hanya Direktur Utama yang bisa mengakses halaman ini.');
        }

        return $next($request);
    }
}
