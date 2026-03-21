<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Services\AI\AiServiceManager;
use App\Services\AI\LlmServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiServiceManager::class);
        $this->app->bind(LlmServiceInterface::class, AiServiceManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS when APP_URL uses https (behind reverse proxy / Cloudflare Tunnel)
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Use Tailwind CSS pagination
        Paginator::useTailwind();

        // Rate limiting for login attempts
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip());
        });

        // Rate limiting for password reset attempts
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(3)->by($request->input('email') . '|' . $request->ip());
        });

        // Rate limiting for 2FA challenge verification attempts
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by((string) $request->session()->get('2fa:user_id') . '|' . $request->ip());
        });
    }
}
