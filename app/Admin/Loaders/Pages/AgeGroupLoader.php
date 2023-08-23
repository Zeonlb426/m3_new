<?php

declare(strict_types = 1);

namespace App\Admin\Loaders\Pages;

use App\Admin\Loaders\AbstractLoader;
use App\Models\AgeGroup;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class AgeGroupLoader
 * @package App\Admin\Loaders\Pages
 */
final class AgeGroupLoader extends AbstractLoader
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBuilder(): Builder
    {
        return AgeGroup::query()->orderBy('min_age')->orderBy('max_age');
    }
}
