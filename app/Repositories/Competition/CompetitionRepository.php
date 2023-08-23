<?php

declare(strict_types=1);

namespace App\Repositories\Competition;

use App\Contracts\AbstractRepository;
use App\Models\Competition\Competition;
use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * Class CompetitionRepository
 * @package App\Repositories
 *
 * @extends \App\Contracts\AbstractRepository<\App\Models\Competition\Competition>
 */
final class CompetitionRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, Competition::class);
    }

    /**
     * @param bool $needResourceContent
     * @param \App\Models\User|null $authUser
     *
     * @return \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition
     */
    public function createApiQuery(bool $needResourceContent = false, ?User $authUser = null): Builder
    {
        $builder = $this->createQuery()
            ->visible()
            ->ordered()
        ;

        if ($needResourceContent) {
            $builder->with([
                'themes.leads',
                'prizes',
                'prizeInfo',
                'leads',
                'masterClasses' => function (BelongsToMany $innerQuery) use ($authUser): BelongsToMany {
                    /* @var \Illuminate\Database\Eloquent\Relations\BelongsToMany|\App\Models\MasterClass\MasterClass $innerQuery */
                    return $innerQuery->withUserLikes($authUser);
                },
                'partners',
            ]);
        }

        return $builder;
    }

    /**
     * @param \App\Models\User $user
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Competition\Competition>
     */
    public function findUserCompetitions(User $user): Collection
    {
        $query = $this->createQuery()
            ->visible()
            ->ordered()
            ->with(['ageGroups', 'media'])
        ;
        $userWorksQuery = $user->works();

        $userWorksQuery = $userWorksQuery
            ->whereRaw(\sprintf(
                '"%s"."competition_id" = "%s"."id"',
                $userWorksQuery->getModel()->getTable(),
                $query->getModel()->getTable(),
            ))
            ->getBaseQuery()
        ;

        return $query
            ->addWhereExistsQuery($userWorksQuery)
            ->get()
        ;
    }

    public function searchCompetitions(string $searchText): Builder
    {
        return $this->createApiQuery()
            ->whereRaw('lower(title) like ?', [\sprintf('%%%s%%', \mb_strtolower($searchText))])
            ->orWhereRaw('lower(content) like ?', [\sprintf('%%%s%%', \mb_strtolower($searchText))])
        ;
    }
}
