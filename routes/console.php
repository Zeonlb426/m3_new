<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Illuminate\Foundation\Console\{
    ClearCompiledCommand,
    ConfigCacheCommand,
    ConfigClearCommand,
    EventCacheCommand,
    EventClearCommand,
    RouteCacheCommand,
    RouteClearCommand,
    ViewCacheCommand,
    ViewClearCommand,
};

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:cache:clear', function () {
    /** @var Command $this */
    $this->call(EventClearCommand::class);
    $this->call(ViewClearCommand::class);
    $this->call(RouteClearCommand::class);
    $this->call(ConfigClearCommand::class);
    $this->call(ClearCompiledCommand::class);
})->describe('Clear all available caches for application.');

Artisan::command('app:cache:rebuild', function () {
    /** @var Command $this */
    $this->call('app:cache:clear');

    $this->call(ViewCacheCommand::class);
    $this->call(ConfigCacheCommand::class);
    $this->call(EventCacheCommand::class);
    $this->call(RouteCacheCommand::class);
})->describe('Rebuild all available caches for application.');
