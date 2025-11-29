<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ThemeController extends Controller
{
    /**
     * Set the theme cookie and return a simple JSON response.
     */
    public function set(Request $request)
    {
        $theme = ($request->input('theme') === 'dark') ? 'dark' : 'light';

        // 10 years in minutes
        $minutes = 60 * 24 * 365 * 10;

        $cookie1 = cookie('agwasuri_theme', $theme, $minutes, '/', null, false, false, false, 'Lax');
        $cookie2 = cookie('theme', $theme, $minutes, '/', null, false, false, false, 'Lax');

        return response()->json(['status' => 'ok', 'theme' => $theme])
            ->withCookie($cookie1)
            ->withCookie($cookie2);
    }
}
