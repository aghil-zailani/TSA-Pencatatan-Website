<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response; // Tambahkan ini untuk tipe return yang lebih modern

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Pengecekan Auth::check() sudah baik, karena middleware 'auth' harusnya dijalankan lebih dulu.
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Cek jika peran user ada di dalam daftar peran yang diizinkan
        if (in_array($user->role, $roles)) {
            return $next($request); // Izinkan request jika peran cocok
        }

        // Jika tidak ada peran yang cocok, alihkan
        return redirect('/home')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}