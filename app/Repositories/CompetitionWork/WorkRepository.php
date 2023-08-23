<?php

declare(strict_types=1);

namespace App\Repositories\CompetitionWork;

use App\Contracts\AbstractRepository;
use App\Models\CompetitionWork\Work;
use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class WorkRepository
 * @package App\Repositories
 *
 * @extends \App\Contracts\AbstractRepository<\App\Models\CompetitionWork\Work>
 */
final class WorkRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, Work::class);
    }

    /**
     * @param bool $needResourceContent
     * @param \App\Models\User|null $authUser
     *
     * @return \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work
     */
    public function createApiQuery(bool $needResourceContent = false, ?User $authUser = null): Builder
    {
        $builder = $this->createQuery()
            ->withoutTrashed()
            ->ordered('desc')
//            ->scopes(\array_filter([
//                false === (false === $needResourceContent && null !== $authUser)
//                    ? Work::$scopeVisible
//                    : null
//                ,
//            ]))
        ;

        if ($needResourceContent) {
            $builder = $builder
                ->with(['author'])
                ->withUserLikes($authUser)
            ;
        }

        return $builder;
    }

    public function searchWorks(string $searchText): Builder
    {
        return $this->createApiQuery(needResourceContent: true, authUser: \Auth::user())
            ->visible()
            ->with(['previewMedia'])
            ->whereHas('author', function ($query) use($searchText) {
                /** @var \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\WorkAuthor $query */
                $query->whereRaw('lower(name) like ?', [\sprintf('%%%s%%', \mb_strtolower($searchText))]);
            })
        ;
    }

    public function countWorkLikesByUser(User $user): int
    {
        return $user->works()
            ->withTrashed()
            ->sum('likes_total_count');
    }
}
