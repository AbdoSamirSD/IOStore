<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocaleFromPrefix
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->segment(1); // Get the first segment of the URL

        if ($locale && in_array($locale, ['en', 'es', 'ar'])) {
            App::setLocale($locale);
            Session::put('locale', $locale);
        } else {
            $locale = Session::get('locale', 'en');
            App::setLocale($locale);
        }

        return $next($request);
    }
}
