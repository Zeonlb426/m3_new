<?php

declare(strict_types=1);

namespace App\Repositories\MasterClass;

use App\Contracts\AbstractRepository;
use App\Models\MasterClass\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CourseRepository
 * @package App\Repositories
 *
 * @extends \App\Contracts\AbstractRepository<Course>
 */
final class CourseRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, Course::class);
    }

    public function createApiQuery(): Builder|Course
    {
        return parent::createQuery()->scopes([Course::$scopeVisible, 'ordered'])->with('leads');
    }

    public function allPaginated(int $limit)
    {
        return $this->createApiQuery()
            ->offsetPaginate($limit)
        ;
    }

    public function byMaserClassId(string $masterClassId)
    {
        return $this->createApiQuery()
            ->whereHas(
                'masterClasses',
                fn($qb)
                => /* @var $qb \Illuminate\Database\Eloquent\Builder */
                $qb->whereKey($masterClassId)
            )
            ->get()
        ;
    }
}
