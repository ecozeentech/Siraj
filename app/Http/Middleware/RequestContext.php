<?php

namespace App\Http\Middleware;

use App\Services\Finance\DisplayCurrency;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($code = $request->query('ref')) {
            $request->session()->put('referral_code', strtoupper(substr((string) $code, 0, 12)));
        }

        $supported = array_keys(config('siraj.supported_locales'));
        $locale = $request->user()?->preferences?->language ?: $request->session()->get('locale', config('app.locale'));
        if (! in_array($locale, $supported, true)) {
            $locale = 'en';
        }
        app()->setLocale($locale);

        if (! $request->session()->has('currency')) {
            $currency = app()->runningUnitTests() ? 'NGN' : app(DisplayCurrency::class)->detect($request);
            $request->session()->put('currency', $currency);
        }

        return $next($request);
    }
}
