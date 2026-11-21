<?php

namespace App\Providers;

use App\Models\Book;
use App\Policies\BookPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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

        RateLimiter::for('uploads', function (Request $request) {
            $max = (int) config('audiolibros.uploads.rate_limit_per_minute', 300);

            return Limit::perMinute($max)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('heavy-preview', function (Request $request) {
            if ($request->user()?->isAdmin()) {
                return Limit::none();
            }

            $max = (int) config('audiolibros.uploads.preview_rate_limit_per_minute', 12);

            return Limit::perMinute($max)->by($request->user()?->id ?: $request->ip());
        });

        if (! $this->app->runningInConsole()) {
            $request = request();

            if ($request->header('X-Forwarded-Proto') === 'https' || $request->secure() || str_contains($request->getHttpHost(), 'motazorrilla.com')) {
                URL::forceScheme('https');
            }

            if ($prefix = $request->header('X-Forwarded-Prefix')) {
                $scheme = ($request->secure() || $request->header('X-Forwarded-Proto') === 'https') ? 'https://' : 'http://';
                URL::forceRootUrl($scheme.$request->getHttpHost().'/'.trim($prefix, '/'));
            }

            if ($baseUrl = $request->getBaseUrl()) {
                config(['app.asset_url' => $baseUrl]);
            }
        }
    }
}
