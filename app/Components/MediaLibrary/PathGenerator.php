<?php

declare(strict_types=1);

namespace App\Components\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

/**
 * Class PathGenerator
 * @package App\Components\MediaLibrary
 */
final class PathGenerator extends DefaultPathGenerator
{
    /**
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
     *
     * @return string
     */
    public function getPath(Media $media): string
    {
        return \sprintf('/%s/', \config('media-library.prefix')) . parent::getPath($media);
    }
}
