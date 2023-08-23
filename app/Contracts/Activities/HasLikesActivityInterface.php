<?php

declare(strict_types=1);

namespace App\Contracts\Activities;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Interface HasLikesActivityInterface
 * @package App\Contracts\Activities
 *
 * @property-read \Illuminate\Support\Collection<\App\Models\User\UserActivity> $likes
 * @property-read int|null $likes_count
 */
interface HasLikesActivityInterface extends HasActivitiesInterface
{
    public function likes(): MorphMany;

    public function incrementLikes(): void;

    public function getLikesCount(): int;
}
