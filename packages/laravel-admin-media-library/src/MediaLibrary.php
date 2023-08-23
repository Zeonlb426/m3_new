<?php

namespace Nicklasos\LaravelAdmin\MediaLibrary;

use Encore\Admin\Extension;
use Encore\Admin\Form;

class MediaLibrary extends Extension
{
    public $name = 'laravel-admin-media-library';

    public static function boot()
    {
        Form::extend('mediaLibrary', MediaLibraryFile::class);
        Form::extend('multipleMediaLibrary', MediaLibraryMultipleFile::class);

        return parent::boot();
    }
}
