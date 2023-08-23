<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Class PointsExchangeSettings
 * @package App\Settings
 */
class PointsExchangeSettings extends Settings
{
    public ?int $exchange_rate;
    public ?int $points_registration;
    public ?int $points_like;
    public ?int $points_work_add;

    public static function group(): string
    {
        return 'point';
    }
}
