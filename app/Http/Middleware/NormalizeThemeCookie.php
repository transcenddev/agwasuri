<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeThemeCookie
{
    /**
     * If the request provides `theme` but not `agwasuri_theme`, ensure the response
     * sets `agwasuri_theme` so server-side checks remain consistent for subsequent requests.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $theme = $request->cookie('theme');
            $ag = $request->cookie('agwasuri_theme');
            if ($theme && ! $ag) {
                // set cookie for 10 years (minutes)
                $minutes = 60 * 24 * 365 * 10;
                $cookie = cookie('agwasuri_theme', $theme, $minutes, '/', null, false, false, false, 'Lax');
                $response->headers->setCookie($cookie);
            }
        } catch (\Exception $e) {
            // swallow — normalization is best-effort
        }

        return $response;
    }
}
