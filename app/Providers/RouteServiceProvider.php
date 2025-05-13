<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Rotaları eski stil kontroller adlarıyla çalıştırabilmek için gerekli
        Route::macro('adminController', function (string $uri, string $controller, array $options = []) {
            return Route::get($uri, "App\\Http\\Controllers\\{$controller}")->name($options['name'] ?? null);
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
        
        // Register custom route middlewares
        Route::aliasMiddleware('admin', \App\Http\Middleware\AdminMiddleware::class);
        Route::aliasMiddleware('staff', \App\Http\Middleware\StaffMiddleware::class);
        Route::aliasMiddleware('librarian', \App\Http\Middleware\LibrarianMiddleware::class);
    }

    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            if (auth()->check()) {
                if (auth()->user()->hasRole('admin')) {
                    return route('admin.dashboard');
                } elseif (auth()->user()->hasRole('staff')) {
                    return route('staff.dashboard');
                }
            }
            return route('login');
        }
    }
} 