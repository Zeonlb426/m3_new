<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Enums\MorphMapperTarget;
use App\Models\User\UserActivity;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read \Illuminate\Support\Collection<\App\Models\User\UserActivity> $activity
 * @property-read int|null $activity_count
 *
 * @implements \App\Contracts\Activities\HasActivitiesInterface
 */
trait HasActivity
{
    abstract public function targetTitle(): string;

    public function targetLabel(): string
    {
        return MorphMapperTarget::tryFrom($this->getMorphClass())?->label() ?? $this->getMorphClass();
    }

    public function activity(): MorphMany
    {
        return $this->morphMany(UserActivity::class, 'interacted')->orderByDesc('created_at');
    }
}
