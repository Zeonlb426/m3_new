<?php

declare(strict_types=1);

namespace App\Repositories\News;

use App\Contracts\AbstractRepository;
use App\Models\News\News;
use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class NewsRepository
 * @package App\Repositories
 *
 * @extends \App\Contracts\AbstractRepository<\App\Models\News\News>
 */
final class NewsRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, News::class);
    }

    /**
     * @param bool $needLikes
     * @param \App\Models\User|null $authUser
     *
     * @return \Illuminate\Database\Eloquent\Builder|\App\Models\News\News
     */
    public function createApiQuery(bool $needLikes = true, ?User $authUser = null): Builder
    {
        $builder = $this->createQuery()
            ->visible()
            ->withoutTrashed()
            ->where('publish_date', '<=', \now())
            ->orderByDesc('publish_date')
        ;

        if ($needLikes) {
            $builder = $builder->withUserLikes($authUser);
        }

        return $builder;
    }

    public function findRelated(
        News $news, int $limit = 3, bool $needLikes = true, ?User $authUser = null,
    ): Collection {
        return $this->createApiQuery($needLikes, $authUser)
            ->with(['media'])
            ->where('id', '!=', $news->id)
            ->where('publish_date', '<=', $news->publish_date)
            ->limit($limit)
            ->get()
        ;
    }

    public function searchNews(string $searchText): Builder
    {
        return $this->createApiQuery(authUser: \Auth::user())
            ->whereRaw('lower(title) like ?', [\sprintf('%%%s%%', \mb_strtolower($searchText))])
            ->orWhereRaw('lower(content) like ?', [\sprintf('%%%s%%', \mb_strtolower($searchText))])
        ;

    }
}
