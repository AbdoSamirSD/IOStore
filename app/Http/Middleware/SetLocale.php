<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the 'lang' header is present in the request
        if ($request->hasHeader('lang')) {
            $locale = $request->header('lang');
        } else {
            // Fallback to default locale
            $locale = Session::get('locale', 'en');
        }

        // Validate if the locale is supported
        if (in_array($locale, ['en', 'ar'])) {
            App::setLocale($locale);
            Session::put('locale', $locale);
        }

        return $next($request);
    }
}
