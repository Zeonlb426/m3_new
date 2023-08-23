<?php

declare(strict_types=1);

namespace App\Jobs\User\Activity;

use App\Enums\User\ActionType;
use App\Jobs\Traits\HasUniqueLike;
use App\Models\User;
use App\Repositories\User\UserActivityRepository;
use App\Services\ActivityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class AddLikeAction implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use HasUniqueLike;

    /**
     * The number of seconds after which the job will no longer stay unique.
     *
     * @var int
     */
    public int $uniqueFor = 60 * 60;

    /**
     * @param \App\Models\User $user
     * @param \Illuminate\Database\Eloquent\Model&\App\Contracts\Activities\HasActivitiesInterface $model
     */
    public function __construct(
        public User $user,
        public Model $model
    ) {
        $this->queue = 'likes';

        $this->user = $this->user->withoutRelations();
        $this->model = $this->model->withoutRelations();
    }

    /**
     * @param \App\Repositories\User\UserActivityRepository $userActivities
     * @param \App\Services\ActivityService $activityService
     *
     * @throws \Throwable
     */
    public function handle(UserActivityRepository $userActivities, ActivityService $activityService): void
    {
        if (false === $userActivities->userActivityExists($this->user, $this->model, ActionType::LIKE)) {
            $activityService->addAction($this->user, $this->model);
        }
    }
}
