<?php

use App\Settings\MainTextsSettings;
use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup(MainTextsSettings::group(), function (SettingsBlueprint $blueprint): void {
            $blueprint->add('meta_title', 'Поколение Маугли');
            $blueprint->add('meta_description', 'Мета описание');
            $blueprint->add('meta_keywords', 'Маугли');
            $blueprint->add('sharing_title', 'PokolenieM3');
            $blueprint->add('sharing_description', 'Описание');
            $blueprint->add('sharing_image', '');
        });
    }
};
