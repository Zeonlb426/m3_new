<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Activities\HasActivitiesInterface;
use App\Contracts\Activities\HasLikesActivityInterface;
use App\Enums\User\ActionType;
use App\Jobs\User\Activity\AddLikeAction;
use App\Jobs\User\Activity\RemoveLikeAction;
use App\Models\User;
use App\Repositories\User\UserActivityRepository;
use App\Repositories\User\UserTotalCreditRepository;
use App\Settings\PointsExchangeSettings;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class ActivityService
{
    public function __construct(
        private readonly UserTotalCreditRepository $userTotalCredits,
        private readonly UserActivityRepository $userActivities,
        private readonly Dispatcher $jobsDispatcher,
    ) {
    }

    public function isUserActivityExists(
        User $user, Model&HasActivitiesInterface $model, ActionType $actionType = ActionType::LIKE,
    ): bool {
        return $this->userActivities->userActivityExists($user, $model, $actionType);
    }

    /**
     * @param \App\Models\User $user
     * @param \Illuminate\Database\Eloquent\Model&\App\Contracts\Activities\HasActivitiesInterface $model
     * @param \App\Enums\User\ActionType $actionType
     *
     * @return bool
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Throwable
     */
    public function addAction(
        User $user, Model&HasActivitiesInterface $model, ActionType $actionType = ActionType::LIKE,
    ): bool {
        $activity = $this->userActivities->findOrNew($user, $model, $actionType);

        $wasActivityTrashed = $activity->trashed();

        if ($activity->exists && false === $wasActivityTrashed) {
            return false;
        }

        if ($wasActivityTrashed) {
            $activity->restore();

            return true;
        }

        $totalCredits = $this->userTotalCredits->findOrCreateByUser($user);

        $pointsSettings = \app()->make(PointsExchangeSettings::class);

        $points = match ($actionType) {
            ActionType::REGISTRATION => (int)$pointsSettings->points_registration,
            ActionType::LIKE => (int)$pointsSettings->points_like,
            ActionType::ADD_WORK => (int)$pointsSettings->points_work_add,
        };

        $credits = $points * (int)$pointsSettings->exchange_rate;

        $activity->point = $points;
        $activity->credits = $credits;

        $this->userActivities->transaction(function () use (
            $activity, $actionType, $totalCredits, $credits, $model,
        ): void {
            $activity->saveOrFail();

            if ($credits) {
                $this->userTotalCredits->addCreditsByType($totalCredits, $actionType, $credits);
            }

            if (ActionType::LIKE === $actionType) {
                /** @var \App\Contracts\Activities\HasLikesActivityInterface $model */
                $model->incrementLikes();
            }
        });

        return true;
    }

    /**
     * @param \App\Models\User $user
     * @param \Illuminate\Database\Eloquent\Model&\App\Contracts\Activities\HasActivitiesInterface $model
     * @param \App\Enums\User\ActionType $actionType
     *
     * @return bool
     *
     * @throws \Throwable
     */
    public function removeAction(
        User $user, Model $model, ActionType $actionType = ActionType::LIKE
    ): bool {
        if (false === \in_array($actionType, [ActionType::LIKE, ActionType::ADD_WORK])) {
            throw new InvalidArgumentException(\sprintf(
                'Invalid action_type "%s" to cancel', $actionType->value
            ));
        }

        $activity = $this->userActivities->findOrNew($user, $model, $actionType);

        if (false === $activity->exists) {
            return false;
        }

        if ($activity->trashed()) {
            return false;
        }

        return $activity->delete();
    }

    /**
     * @param \App\Models\User $user
     * @param \Illuminate\Database\Eloquent\Model&\App\Contracts\Activities\HasLikesActivityInterface $model
     *
     * @return bool
     *
     * @throws \Throwable
     */
    public function triggerLikeAsync(User $user, Model&HasLikesActivityInterface $model): bool
    {
        $activityExists = $this->isUserActivityExists($user, $model);

        if ($activityExists) {
            $job = new RemoveLikeAction($user, $model);
            $hasLike = false;
        } else {
            $job = new AddLikeAction($user, $model);
            $hasLike = true;
        }

        $this->jobsDispatcher->dispatch($job);

        return $hasLike;
    }
}
