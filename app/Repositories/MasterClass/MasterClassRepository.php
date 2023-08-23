<?php

declare(strict_types=1);

namespace App\Repositories\MasterClass;

use App\Contracts\AbstractRepository;
use App\Models\MasterClass\MasterClass;
use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class MasterClassRepository
 * @package App\Repositories
 *
 * @extends \App\Contracts\AbstractRepository<\App\Models\MasterClass\MasterClass>
 */
final class MasterClassRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, MasterClass::class);
    }

    /**
     * @param bool $needResourceContent
     * @param \App\Models\User|null $authUser
     *
     * @return \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass
     */
    public function createApiQuery(bool $needResourceContent = true, ?User $authUser = null): Builder
    {
        $builder = $this->createQuery()
            ->visible()
            ->ordered()
        ;

        if ($needResourceContent) {
            $builder = $builder
                ->with(['lead'])
                ->withUserLikes($authUser)
            ;
        }

        return $builder;
    }

    public function searchMasterClass(string $searchText): Builder
    {
        return $this->createApiQuery(authUser: \Auth::user())
            ->whereRaw('lower(title) like ?', [\sprintf('%%%s%%', \mb_strtolower($searchText))])
            ->orWhereRaw('lower(content) like ?', [\sprintf('%%%s%%', \mb_strtolower($searchText))])
        ;
    }
}
