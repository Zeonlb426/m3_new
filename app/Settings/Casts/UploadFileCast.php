<?php

declare(strict_types = 1);

namespace App\Settings\Casts;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelSettings\SettingsCasts\SettingsCast;

/**
 * Class UploadFileCast
 * @package App\Settings\Casts
 */
final class UploadFileCast implements SettingsCast
{
    private string $property;
    private string $selfClass;

    public function __construct(string $property, string $selfClass)
    {
        $this->property = $property;
        $this->selfClass = $selfClass;
    }

    public function get($payload): ?string
    {
        return $payload;
    }

    /**
     * @param $payload
     * @return string|null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function set($payload)
    {
        if ($payload instanceof UploadedFile) {
            /* @var $payload \Illuminate\Http\ */

            $path = \sprintf('/%s/settings/files/%s/', \config('media-library.prefix'), $this->property);
            \Storage::disk()->deleteDirectory($path);
            $file = \sprintf('%s/%s.%s', $path, \uniqid(), $payload->guessExtension());
            \Storage::disk()->put($file, $payload->getContent());

            return $file;
        } elseif (empty($payload)) {
            $path = \sprintf('/%s/settings/files/%s/', \config('media-library.prefix'), $this->property);
            \Storage::disk()->deleteDirectory($path);

            return null;
        } else {
            return \app()->make($this->selfClass)->refresh()->{$this->property};
        }
    }
}
