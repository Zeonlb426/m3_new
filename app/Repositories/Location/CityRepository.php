<?php

declare(strict_types=1);

namespace App\Repositories\Location;

use App\Contracts\AbstractRepository;
use App\Models\Location\City;
use App\Models\Location\Region;
use Illuminate\Database\Connection;

/**
 * Class CityRepository
 * @package App\Repositories\Location
 *
 * @extends \App\Contracts\AbstractRepository<City>
 */
final class CityRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, City::class);
    }

    /**
     * @param \App\Models\Location\Region $region
     * @param string $title
     * @return \App\Models\Location\City|null
     * @throws \Throwable
     */
    public function create(Region $region, string $title): ?City
    {
        $model = $this->createModel();
        $model->title = $title;
        $model->region()->associate($region);
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

    /**
     * @param string $regionId
     * @param int $limit
     * @return mixed
     */
    public function byRegionPaginated(string $regionId, int $limit): mixed
    {
        return $this->createQuery()
            ->orderBy('title')
            ->where('region_id', $regionId)
            ->with('region')
            ->offsetPaginate($limit)
        ;
    }

    public function byRegionWithoutPagination(int $regionId): mixed
    {
        return $this->createQuery()
            ->orderBy('title')
            ->where('region_id', $regionId)
            ->with('region')
            ->get()
        ;
    }
}
