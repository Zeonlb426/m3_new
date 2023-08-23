<?php

declare(strict_types = 1);

namespace App\Admin\Loaders\Pages;

use App\Admin\Loaders\AbstractLoader;
use App\Models\MasterClass\MasterClass;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class MasterClassLoader
 * @package App\Admin\Loaders\Pages
 */
final class MasterClassLoader extends AbstractLoader
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBuilder(): Builder
    {
        return MasterClass::query()->visible()->withoutTrashed();
    }
}
