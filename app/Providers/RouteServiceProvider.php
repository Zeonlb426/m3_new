<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\User\SocialProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

/**
 * Class RouteServiceProvider
 * @package App\Providers
 */
final class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    protected $apiNamespace = 'App\\Http\\Controllers\\Api';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        URL::forceRootUrl(\config('app.url'));
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(\base_path('routes/api.php'))
            ;

            Route::middleware('web')
                ->group(\base_path('routes/web.php'))
            ;
        });

        Request::macro('isApiRequest', function (): bool {
            /** @var \Illuminate\Http\Request $this */
            return $this->expectsJson() || \str_starts_with($this->path(), 'api');
        });

        $this->app->booted(function () {
            Config::set('services.vkontakte.redirect', \url(sprintf('auth/social/%s', SocialProvider::VK->value)));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', static function (Request $request) {
            return Limit::perMinute(60)->by(\optional($request->user())->id ?: $request->ip());
        });
    }
}
