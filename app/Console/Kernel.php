<?php

namespace App\Console;

use App\Utils\DebugUtils;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel
 * @package App\Console
 */
final class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [

    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        if (DebugUtils::telescopeEnabledByAdditionalEnvironments(\request())
            && \config('telescope.enabled')
        ) {
            $schedule
                ->command('telescope:prune', [
                    '--hours' => 48,
                ])
                ->dailyAt('00:00')
            ;
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require \base_path('routes/console.php');
    }
}
