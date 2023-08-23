<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Class CountersSettings
 * @package App\Settings
 */
class CountersSettings extends Settings
{
    public ?int $fake_credits;
    public ?int $fake_likes;

    public static function group(): string
    {
        return 'misc';
    }
}
