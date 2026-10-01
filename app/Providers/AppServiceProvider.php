<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->header('X-Forwarded-Proto') === 'https' || request()->secure() || str_contains(request()->getHttpHost(), 'motazorrilla.com')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        if ($prefix = request()->header('X-Forwarded-Prefix')) {
            $scheme = (request()->secure() || request()->header('X-Forwarded-Proto') === 'https') ? 'https://' : 'http://';
            \Illuminate\Support\Facades\URL::forceRootUrl($scheme . request()->getHttpHost() . '/' . trim($prefix, '/'));
        }
    }
}
