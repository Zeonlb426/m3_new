<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Contracts\AbstractRepository;
use App\Contracts\Activities\HasActivitiesInterface;
use App\Enums\User\ActionType;
use App\Models\User;
use App\Models\User\UserActivity;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

/**
 * Class UserActivityRepository
 * @package App\Repositories
 *
 * @extends \App\Contracts\AbstractRepository<\App\Models\User\UserActivity>
 */
final class UserActivityRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, UserActivity::class);
    }

    /**
     * @param \App\Models\User $user
     * @param \Illuminate\Database\Eloquent\Model&\App\Contracts\Activities\HasActivitiesInterface $model
     * @param \App\Enums\User\ActionType $actionType
     *
     * @return bool
     */
    public function userActivityExists(
        User $user, Model&HasActivitiesInterface $model, ActionType $actionType,
    ): bool {
        return $model
            ->activity()
            ->withoutTrashed()
            ->where('user_id', $user->getKey())
            ->where('action_type', $actionType->value)
            ->exists()
        ;
    }

    /**
     * @param \App\Models\User $user
     * @param \Illuminate\Database\Eloquent\Model&\App\Contracts\Activities\HasActivitiesInterface $model
     * @param \App\Enums\User\ActionType $actionType
     *
     * @return \App\Models\User\UserActivity
     */
    public function findOrNew(User $user, Model $model, ActionType $actionType): User\UserActivity
    {
        return $model
            ->activity()
            ->withTrashed()
            ->firstOrNew(
                ['user_id' => $user->getKey()],
                [
                    'action_type' => $actionType->value,
                    'interacted_id' => $model->getKey(),
                    'interacted_type' => $model->getMorphClass(),
                ]
            )
        ;
    }

    public static function getActivitiesCountQuery(ActionType $actionType = ActionType::LIKE): Builder
    {
        return UserActivity::query()
            ->from((new UserActivity())->getTable(), 'ua')
            ->selectRaw('COUNT(*)')
            ->where(['ua.action_type' => $actionType->value])
            ->withoutGlobalScope(SoftDeletingScope::class)
        ;
    }

    public static function getTopFiveUsers(): Collection
    {
        return User::select(['users.*', 'user_total_credits.count_total as count_total'])
            ->join('user_total_credits', 'users.id', '=', 'user_total_credits.user_id')
            ->orderByDesc('user_total_credits.count_total')
            ->limit(5)
            ->get()
        ;
    }
}
