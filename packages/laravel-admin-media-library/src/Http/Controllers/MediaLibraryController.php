<?php

namespace Nicklasos\LaravelAdmin\MediaLibrary\Http\Controllers;

use Illuminate\Routing\Controller;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class MediaLibraryController extends Controller
{
    public function download($id)
    {
        return Media::findOrFail($id);
    }
}
