<?php

declare(strict_types=1);

namespace App\Providers;

use App\Utils\DebugUtils;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;

/**
 * Class TelescopeApplicationServiceProvider
 * @package App\Providers
 */
final class TelescopeApplicationServiceProvider extends \Laravel\Telescope\TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            if ($this->app->environment('local')) {
                return true;
            }

            return $entry->isReportableException() ||
                $entry->isRequest() ||
                $entry->isQuery() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     *
     * @return void
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Configure the Telescope authorization services.
     */
    protected function authorization(): void
    {
        $this->gate();

        Telescope::auth(static function ($request): bool {
            return \app()->environment('local')
                || DebugUtils::telescopeEnabledByAdditionalEnvironments(\request())
                || Gate::check('viewTelescope', [$request->user()]);
        });
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return \in_array($user->email, [

            ]);
        });
    }
}
