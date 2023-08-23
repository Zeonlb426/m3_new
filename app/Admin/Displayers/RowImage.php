<?php

declare(strict_types = 1);

namespace App\Admin\Displayers;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Storage;

final class RowImage extends AbstractDisplayer
{
    public function display($server = '', $width = 75, $height = 75, ?string $defaultImage = null)
    {
        if ($this->value instanceof Arrayable) {
            $this->value = $this->value->toArray();
        }

        $values = collect((array) $this->value)->filter();
        if ($values->isEmpty()) {
            return $defaultImage
                ? "<img alt='image' src='$defaultImage' style='width:100px;max-width:{$width}px;height:100px;max-height:{$height}px;object-fit: cover' class='img img-thumbnail' />"
                : \__('admin.messages.empty_value')
            ;
        }
        return $values->map(function ($path) use ($server, $width, $height, $defaultImage) {
            $path = $path ?: $defaultImage;
            if (url()->isValidUrl($path) || \str_starts_with($path, 'data:image')) {
                $src = $path;
            } elseif ($server) {
                $src = \rtrim($server, '/').'/'.ltrim($path, '/');
            } else {
                $src = Storage::disk(config('admin.upload.disk'))->url($path);
            }

            return "<img alt='image' src='$src' style='width:100px;max-width:{$width}px;height:100px;max-height:{$height}px;object-fit: cover' class='img img-thumbnail' />";
        })->implode('&nbsp;');
    }
}
