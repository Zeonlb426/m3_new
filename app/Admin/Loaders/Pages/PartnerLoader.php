<?php

declare(strict_types = 1);

namespace App\Admin\Loaders\Pages;

use App\Admin\Loaders\AbstractLoader;
use App\Models\Competition\Partner;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class PartnerLoader
 * @package App\Admin\Loaders\Pages
 */
final class PartnerLoader extends AbstractLoader
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBuilder(): Builder
    {
        return Partner::query()->visible();
    }
}
