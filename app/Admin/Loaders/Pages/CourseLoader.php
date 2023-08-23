<?php

declare(strict_types = 1);

namespace App\Admin\Loaders\Pages;

use App\Admin\Loaders\AbstractLoader;
use App\Models\MasterClass\Course;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CourseLoader
 * @package App\Admin\Loaders\Pages
 */
final class CourseLoader extends AbstractLoader
{
    protected function getSearchKey(): string
    {
        return 'name';
    }

    protected function getColumns(): array
    {
        return [
            'id' => 'id',
            'name' => 'text',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBuilder(): Builder
    {
        return Course::query()->visible()->ordered();
    }
}
