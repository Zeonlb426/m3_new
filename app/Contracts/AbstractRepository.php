<?php

declare(strict_types=1);

namespace App\Contracts;

use Dvlp\ExtendedPagination\OffsetPaginator;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Class AbstractRepository
 * @package App\Contracts
 *
 * @template T of \Illuminate\Database\Eloquent\Model
 */
abstract class AbstractRepository
{
    /**
     * @var class-string<T>
     */
    private string $modelClass;

    /**
     * @var \Illuminate\Database\Connection
     */
    private Connection $connection;

    /**
     * AbstractRepository constructor.
     * @param \Illuminate\Database\Connection $connection
     * @param class-string<T> $modelClass
     */
    public function __construct(Connection $connection, string $modelClass)
    {
        if (!\class_exists($modelClass)) {
            throw new \InvalidArgumentException(
                \sprintf('Argument $modelClass "%s" is not valid classname.', $modelClass)
            );
        }

        if (false === \is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Argument $modelClass "%s" must be extended from "%s" class',
                    $modelClass,
                    Model::class
                )
            );
        }

        $this->modelClass = $modelClass;
        $this->connection = $connection;
    }

    /**
     * @return \Illuminate\Database\Connection
     */
    protected function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @return Model|T
     */
    public function createModel(): Model
    {
        return new $this->modelClass;
    }

    /**
     * @param array $condition
     * @param string|null $orderAttribute
     * @param string $orderDirection
     * @param int $limit
     * @param int $offset
     * @param array $with
     * @param array $withCount
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findBy(
        array $condition,
        string $orderAttribute = null,
        string $orderDirection = 'asc',
        int $limit = -1,
        int $offset = 0,
        array $with = [],
        array $withCount = []
    ): Collection {
        $query = $this->createQuery();

        $query->where($condition);

        if (null !== $orderAttribute) {
            $query->orderBy($orderAttribute, $orderDirection);
        }

        if (false === empty($with)) {
            $query->with($with);
        }

        if (false === empty($withCount)) {
            $query->withCount($withCount);
        }

        $query->limit($limit);
        $query->offset($offset);

        return $query->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|T
     */
    protected function createQuery(): Builder
    {
        return \call_user_func([$this->modelClass, 'query']);
    }

    /**
     * @param array $condition
     * @param string|null $orderAttribute
     * @param string $orderDirection
     * @param int $perPage
     * @param array $with
     * @param array $withCount
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginate(
        array $condition,
        string $orderAttribute = null,
        string $orderDirection = 'asc',
        int $perPage = 20,
        array $with = [],
        array $withCount = []
    ): LengthAwarePaginator {
        $query = $this->createQuery();

        $query->where($condition);

        if (null !== $orderAttribute) {
            $query->orderBy($orderAttribute, $orderDirection);
        }

        if (false === empty($with)) {
            $query->with($with);
        }

        if (false === empty($withCount)) {
            $query->withCount($withCount);
        }

        return $query->paginate($perPage);
    }

    /**
     * @param array $condition
     * @param string|null $orderAttribute
     * @param string $orderDirection
     * @param array $with
     * @param array $withCount
     * @param array $allowedSorts
     * @param array $allowedFilters
     *
     * @return \Dvlp\ExtendedPagination\OffsetPaginator
     */
    public function offsetPaginate(
        array $condition,
        ?string $orderAttribute = null,
        string $orderDirection = 'asc',
        array $with = [],
        array $withCount = [],
        array $allowedSorts = [],
        array $allowedFilters = []
    ): OffsetPaginator {
        $query = $this->createQuery();

        $query->where($condition);

        if (null !== $orderAttribute) {
            $query->orderBy($orderAttribute, $orderDirection);
        }

        if (false === empty($with)) {
            $query->with($with);
        }

        if (false === empty($withCount)) {
            $query->withCount($withCount);
        }

        if (false === empty($allowedSorts)) {
            QueryBuilder::for($query)
                ->allowedSorts(\array_values($allowedSorts));
        }

        if (false === empty($allowedFilters)) {
            $filters = [];
            foreach ($allowedFilters as $name => $internalName) {
                $filterName = \is_string($name) ? $name : $internalName;
                $filters[] = AllowedFilter::exact($filterName, $internalName);
            }
            QueryBuilder::for($query)
                ->allowedFilters($filters);
        }

        return $query->offsetPaginate();
    }

    /**
     * @param array $condition
     * @param array $select
     * @param string|null $orderAttribute
     * @param string $orderDirection
     * @param array $with
     * @param array $withCount
     *
     * @return \Illuminate\Database\Eloquent\Model|T|null
     */
    public function findOneBy(
        array $condition,
        array $select = ['*'],
        string $orderAttribute = null,
        string $orderDirection = 'asc',
        array $with = [],
        array $withCount = [],
    ): ?Model {
        $query = $this->createQuery();

        $query->where($condition);

        if (null !== $orderAttribute) {
            $query->orderBy($orderAttribute, $orderDirection);
        }

        if (false === empty($with)) {
            $query->with($with);
        }

        return $query->first();
    }

    /**
     * @param array $condition
     *
     * @return bool
     */
    public function exists(array $condition): bool
    {
        $query = $this->createQuery();

        $query->where($condition);

        return $query->exists();
    }

    /**
     * @param array $condition
     *
     * @return int
     */
    public function count(array $condition): int
    {
        $query = $this->createQuery();

        $query->where($condition);

        return $query->count();
    }

    /**
     * @param array $condition
     * @param string $column
     *
     * @return mixed
     */
    public function max(array $condition, string $column)
    {
        $query = $this->createQuery();

        $query->where($condition);

        return $query->max($column);
    }

    /**
     * @param array $condition
     * @param string $column
     *
     * @return mixed
     */
    public function min(array $condition, string $column)
    {
        $query = $this->createQuery();

        $query->where($condition);

        return $query->min($column);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model|T $model
     *
     * @return bool
     *
     * @throws \Throwable
     */
    public function save(Model $model): bool
    {
        return $model->saveOrFail();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model|T $model
     *
     * @return bool|null
     *
     * @throws \Throwable
     */
    public function delete(Model $model): ?bool
    {
        return $model->delete();
    }

    /**
     * @param callable $callable
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transaction(callable $callable)
    {
        return $this->connection->transaction($callable);
    }

    /**
     * @param \Illuminate\Database\QueryException $exception
     *
     * @return bool
     */
    public function isUniqueConstraint(QueryException $exception): bool
    {
        // It works only in postgres 9 and upper.
        return \str_contains($exception->getMessage(), 'duplicate key value');
    }

    /**
     * @param \Illuminate\Database\QueryException $exception
     *
     * @return void
     */
    public function throwIfNotUnique(QueryException $exception): void
    {
        if (false === $this->isUniqueConstraint($exception)) {
            throw $exception;
        }
    }
}
