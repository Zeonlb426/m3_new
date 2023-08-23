<?php

declare(strict_types = 1);

namespace App\Admin\Loaders\Pages;

use App\Admin\Loaders\AbstractLoader;
use App\Models\Location\City;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CityLoader
 * @package App\Admin\Loaders\Pages
 */
final class CityLoader extends AbstractLoader
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBuilder(): Builder
    {
        if (false === empty($region = \trim(\request('region_id', '')))) {
            return City::query()->whereRegionId($region);
        }

        return City::query();
    }
}
