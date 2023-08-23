<?php

declare(strict_types=1);

namespace App\Contracts\Activities;

/**
 * Interface HasActivitiesInterface
 * @package App\Contracts\Activities
 *
 * @property-read \Illuminate\Support\Collection<\App\Models\User\UserActivity> $activity
 * @property-read int|null $activity_count
 *
 * @method \Illuminate\Database\Eloquent\Relations\HasOneOrMany|\App\Models\User\UserActivity activity()
 */
interface HasActivitiesInterface
{

}
