<?php

declare(strict_types = 1);

namespace App\Admin\Loaders\Pages;

use App\Admin\Loaders\AbstractLoader;
use App\Models\Competition\Theme;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ThemeLoader
 * @package App\Admin\Loaders\Pages
 */
final class ThemeLoader extends AbstractLoader
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBuilder(): Builder
    {
        return Theme::query();
    }
}
