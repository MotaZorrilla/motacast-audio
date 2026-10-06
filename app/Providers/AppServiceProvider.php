<?php

namespace App\Providers;

use App\Models\Book;
use App\Policies\BookPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        Gate::policy(Book::class, BookPolicy::class);

        if (request()->header('X-Forwarded-Proto') === 'https' || request()->secure() || str_contains(request()->getHttpHost(), 'motazorrilla.com')) {
            URL::forceScheme('https');
        }

        if ($prefix = request()->header('X-Forwarded-Prefix')) {
            $scheme = (request()->secure() || request()->header('X-Forwarded-Proto') === 'https') ? 'https://' : 'http://';
            URL::forceRootUrl($scheme.request()->getHttpHost().'/'.trim($prefix, '/'));
        }
    }
}
