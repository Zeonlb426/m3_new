<?php

declare(strict_types=1);

namespace App\Repositories\Promo;

use App\Contracts\AbstractRepository;
use App\Models\Promo\SuccessHistory;
use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class SuccessHistoryRepository
 * @package App\Repositories
 *
 * @extends AbstractRepository<\App\Models\Promo\SuccessHistory>
 */
final class SuccessHistoryRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, SuccessHistory::class);
    }

    /**
     * @param bool $needLikes
     * @param \App\Models\User|null $authUser
     *
     * @return \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory
     */
    public function createApiQuery(bool $needLikes = true, ?User $authUser = null): Builder
    {
        $builder = $this->createQuery()
            ->visible()
            ->ordered()
            ->with('sharing')
        ;

        if ($needLikes) {
            $builder = $builder->withUserLikes($authUser);
        }

        return $builder;
    }

    public function allPaginated(int $limit, ?User $authUser = null)
    {
        return $this->createApiQuery(authUser: $authUser)->offsetPaginate($limit);
    }
}
