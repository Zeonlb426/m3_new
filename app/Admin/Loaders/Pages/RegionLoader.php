<?php

declare(strict_types = 1);

namespace App\Admin\Loaders\Pages;

use App\Admin\Loaders\AbstractLoader;
use App\Models\Location\Region;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class RegionLoader
 * @package App\Admin\Loaders\Pages
 */
final class RegionLoader extends AbstractLoader
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBuilder(): Builder
    {
        return Region::query();
    }
}
