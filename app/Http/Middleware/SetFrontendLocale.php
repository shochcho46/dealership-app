<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/** Applies the visitor's chosen language (en / bn) to public website routes only. */
class SetFrontendLocale
{
    public const LOCALES = ['en', 'bn'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('frontend_locale', 'en');

        App::setLocale(in_array($locale, self::LOCALES, true) ? $locale : 'en');

        return $next($request);
    }
}
