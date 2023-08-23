<?php

declare(strict_types = 1);

namespace App\Admin\Loaders\Pages;

use App\Admin\Loaders\AbstractLoader;
use App\Models\Competition\Competition;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CompetitionLoader
 * @package App\Admin\Loaders\Pages
 */
final class CompetitionLoader extends AbstractLoader
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBuilder(): Builder
    {
        return Competition::query();
    }
}
