<?php

declare(strict_types = 1);

namespace App\Models\Traits;

use App\Models\Sharing;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read \App\Models\Sharing|null $sharing
 */
trait HasSharing
{
    public function sharing(): MorphOne
    {
        return $this->morphOne(Sharing::class, 'shared');
    }

    public static function bootHasSharing()
    {
        static::deleting(function(self $model) {
            $model->sharing()->delete();
        });
    }
}
