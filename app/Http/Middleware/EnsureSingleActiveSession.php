<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleActiveSession
{
    public const CACHE_KEY = 'auth.single_active_session_id';

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $sessionId = $request->session()->getId();
        $activeSessionId = Cache::get(self::CACHE_KEY);

        if ($activeSessionId && !hash_equals($activeSessionId, $sessionId)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with(
                'loginError',
                'Sesi Anda telah berakhir karena akun digunakan untuk login di sesi lain.'
            );
        }

        self::remember($sessionId);

        return $next($request);
    }

    public static function remember(string $sessionId): void
    {
        Cache::put(
            self::CACHE_KEY,
            $sessionId,
            now()->addMinutes(config('session.lifetime'))
        );
    }

    public static function forgetIfCurrent(string $sessionId): void
    {
        if (Cache::get(self::CACHE_KEY) === $sessionId) {
            Cache::forget(self::CACHE_KEY);
        }
    }
}