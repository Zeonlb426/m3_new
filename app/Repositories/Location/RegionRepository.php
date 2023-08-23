<?php

declare(strict_types=1);

namespace App\Repositories\Location;

use App\Contracts\AbstractRepository;
use App\Models\Location\Region;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;

/**
 * Class RegionRepository
 * @package App\Repositories\Location
 *
 * @extends \App\Contracts\AbstractRepository<Region>
 */
final class RegionRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, Region::class);
    }

    /**
     * @param string $title
     * @param string $code
     * @return \App\Models\Location\Region|null
     * @throws \Throwable
     */
    public function create(string $title, string $code): ?Region
    {
        $model = $this->createModel();
        $model->title = $title;
        $model->code = $code;
        $this->save($model);

        return $model;
    }

    /**
     * @param int $limit
     * @return mixed
     */
    public function allPaginated(int $limit): mixed
    {
        return $this->createQuery()
            ->orderBy('title')
            ->offsetPaginate($limit)
        ;
    }

    public function withoutPagination(): mixed
    {
        return $this->createQuery()
            ->orderBy('title')
            ->get()
        ;
    }
}
