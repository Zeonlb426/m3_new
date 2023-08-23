<?php

declare(strict_types=1);

namespace App\Components\MediaLibrary;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\Support\UrlGenerator\BaseUrlGenerator;

/**
 * Class UrlGenerator
 * @package App\Components\MediaLibrary
 */
final class UrlGenerator extends BaseUrlGenerator
{
    /**
     * @return string
     */
    public function getUrl(): string
    {
        return \config('media-library.aws_url') . $this->versionUrl($this->getPathRelativeToRoot());
    }

    /**
     * @param \DateTimeInterface $expiration
     * @param array $options
     *
     * @return string
     */
    public function getTemporaryUrl(\DateTimeInterface $expiration, array $options = []): string
    {
        return $this->getDisk()->temporaryUrl($this->getPath(), $expiration, $options);
    }

    /**
     * @return string
     */
    public function getBaseMediaDirectoryUrl(): string
    {
        return $this->getDisk()->url('/');
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        $adapter = $this->getDisk()->getAdapter();

        $cachedAdapter = '\League\Flysystem\Cached\CachedAdapter';

        if ($adapter instanceof $cachedAdapter) {
            $adapter = $adapter->getAdapter();
        }

        $pathPrefix = $adapter->getPathPrefix();

        return $pathPrefix . $this->getPathRelativeToRoot();
    }

    /**
     * @return string
     */
    public function getResponsiveImagesDirectoryUrl(): string
    {
        $base = Str::finish($this->getBaseMediaDirectoryUrl(), '/');
        $path = null;

        if (null !== $this->pathGenerator && null !== $this->media) {
            $path = $this->pathGenerator->getPathForResponsiveImages($this->media);
        }

        return Str::finish(\url($base . $path), '/');
    }
}
