<?php

use App\Settings\PointsExchangeSettings;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup(PointsExchangeSettings::group(), function (SettingsBlueprint $blueprint): void {
            $blueprint->add('exchange_rate', 1);
            $blueprint->add('points_registration', 1);
            $blueprint->add('points_like', 1);
            $blueprint->add('points_work_add', 1);
        });
    }
};
