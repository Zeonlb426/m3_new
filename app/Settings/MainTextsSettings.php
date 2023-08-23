<?php

namespace App\Settings;

use App\Settings\Casts\UploadFileCast;
use Illuminate\Http\UploadedFile;
use Spatie\LaravelSettings\Settings;

/**
 * Class MainTextsSettings
 * @package App\Settings
 */
class MainTextsSettings extends Settings
{
    public ?string $meta_title;
    public ?string $meta_description;
    public ?string $meta_keywords;
    public ?string $sharing_title;
    public ?string $sharing_description;
    public null|string|UploadedFile $sharing_image;

    public static function group(): string
    {
        return 'text';
    }

    public static function casts(): array
    {
        return [
            'sharing_image' => new UploadFileCast('sharing_image', static::class)
        ];
    }
}
