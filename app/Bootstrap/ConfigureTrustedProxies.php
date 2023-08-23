<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Http\Middleware\TrustProxies;
use Illuminate\Contracts\Foundation\Application;

/**
 * Class ConfigureTrustedProxies
 * @package App\Bootstrap
 */
final class ConfigureTrustedProxies
{
    /**
     * @param \Illuminate\Contracts\Foundation\Application $application
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function bootstrap(Application $application): void
    {
        $request = $application->get('request');
        // переиспользуем middleware, чтобы два раза не дублировать функционал
        $middleware = $application->make(TrustProxies::class);

        $middleware->handle($request, static function (): void {
        });
    }
}
