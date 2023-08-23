<?php

declare(strict_types = 1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder|self visible()
 */
trait UseVisibleStatus
{
    public static string $scopeVisible = 'visible';

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible(Builder $query): Builder
    {
        /* @var $query self */
        return $query->whereVisibleStatus(true);
    }
}
