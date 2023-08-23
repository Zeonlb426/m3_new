<?php

use App\Settings\CountersSettings;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup(CountersSettings::group(), function (SettingsBlueprint $blueprint): void {
            $blueprint->add('fake_credits', 23241405);
            $blueprint->add('fake_likes', 5029128);
        });
    }
};
