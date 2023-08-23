<?php

declare(strict_types=1);

namespace App\Components\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class IdPathGenerator implements \Spatie\MediaLibrary\Support\PathGenerator\PathGenerator
{
    public function getPath(Media $media): string
    {
        return \implode('/', [
            \config('media-library.prefix'),
            $this->getBasePath($media),
        ]);
    }

    public function getPathForConversions(Media $media): string
    {
        return \implode('/', [
            \config('media-library.prefix'),
            'conversions',
            $this->getBasePath($media),
        ]);
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return \implode('/', [
            \config('media-library.prefix'),
            'responsive-images',
            $this->getBasePath($media),
        ]);
    }

    protected function getBasePath(Media $media): string
    {
        $classDirectory = \strtolower(\class_basename($media->model_type));

        $collection = $media->collection_name ?: 'media';
        $id = $media->getKey();

        $idDir0 = (int)($id / 1000000);
        $idDir1 = (int)($id / 1000) % 1000;
        $idDir2 = (int)$id % 1000;

        return implode('/', [
            $classDirectory,
            $collection,
            $idDir0,
            $idDir1,
            $idDir2,
            $id,
            '', // seems like path must end with '/', so don't erase this one
        ]);
    }
}
