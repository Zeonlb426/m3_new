<?php

return [
    'routes' => [
        'path' => 'schedule-monitor',
        'middleware' => \config('admin.route.middleware')
    ],
    'controller' => \Dvlp\LaravelScheduleMonitor\Admin\Controllers\ScheduleMonitorController::class,
    'exclude_commands' => [

    ],
];
