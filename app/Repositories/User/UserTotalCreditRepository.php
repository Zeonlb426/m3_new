<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Contracts\AbstractRepository;
use App\Enums\User\ActionType;
use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;

/**
 * Class UserTotalCreditRepository
 * @package App\Repositories\User
 *
 * @extends \App\Contracts\AbstractRepository<\App\Models\User\UserTotalCredit>
 */
final class UserTotalCreditRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, User\UserTotalCredit::class);
    }

    public function create(User $user): User\UserTotalCredit
    {
        $totalCredit = new User\UserTotalCredit();

        $totalCredit->user()->associate($user);

        $totalCredit->save();

        $user->setRelation('totalCredit', $totalCredit);

        return $totalCredit;
    }

    /**
     * @param \App\Models\User $user
     *
     * @return \App\Models\User\UserTotalCredit
     *
     * @throws \Throwable
     */
    public function findOrCreateByUser(User $user): User\UserTotalCredit
    {
        $totalCredit = $this->findOneBy(['user_id' => $user->id]);

        if (null !== $totalCredit) {
            return $totalCredit;
        }

        try {
            return $this->create($user);
        } catch (QueryException $exception) {
            $this->throwIfNotUnique($exception);
        }

        return $this->findOneBy(['user_id' => $user->id]);
    }

    public function addCreditsByType(User\UserTotalCredit $model, ActionType $type, int $credits): void
    {
        $updateField = $this->creditFieldByActionType($type);

        $this->createQuery()
            ->where(['id' => $model->id])
            ->update([
                $updateField => \DB::raw(\sprintf('"%s" + %d', $updateField, $credits)),
                'count_total' => \DB::raw(\sprintf('"count_total" + %d', $credits)),
            ])
        ;
    }

    private function creditFieldByActionType(ActionType $type): string
    {
        return match ($type) {
            ActionType::REGISTRATION => 'count_register',
            ActionType::LIKE => 'count_likes',
            ActionType::ADD_WORK => 'count_works',
        };
    }
}
