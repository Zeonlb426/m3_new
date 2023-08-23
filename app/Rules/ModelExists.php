<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ModelExists
 * @package App\Rules
 *
 * @template T of \Illuminate\Database\Eloquent\Model
 */
final class ModelExists implements Rule
{
    /**
     * The array of custom query callbacks.
     *
     * @var array<Closure(Builder|T): Builder>
     */
    protected array $using = [];

    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\SoftDeletes|T
     */
    protected $query;

    /**
     * @var (\Closure(T|\Illuminate\Support\Collection<T>): null)|null
     */
    protected ?Closure $resultSetter = null;

    /**
     * ModelExists constructor.
     *
     * @param class-string<T> $modelClass
     * @param string|null $column
     */
    public function __construct(
        protected readonly string $modelClass,
        protected readonly ?string $column = null
    ) {
    }

    public function passes($attribute, $value): bool
    {
        $this->query = \call_user_func([$this->modelClass, 'query']);

        if (\is_array($value)) {
            $this->query = $this->query->whereIn(
                $this->column ?? $this->query->getModel()->getKeyName(), $value
            );
        } else {
            $this->query = $this->query->where(
                $this->column ?? $this->query->getModel()->getKeyName(), '=', $value
            );
        }

        foreach ($this->using as $callback) {
            $this->query = $callback($this->query);
        }

        if (\is_array($value)) {
            $result = $this->query->get();
        } else {
            $result = $this->query->first();
        }

        if ((\is_array($value) && \count($value) < $result->count())
            || null === $result
        ) {
            return false;
        }

        if (null !== $this->resultSetter) {
            ($this->resultSetter)($result);
        }

        return true;
    }

    public function message(): string
    {
        return \__('validation.exists');
    }

    /**
     * Set a "where" constraint on the query.
     *
     * @param \Closure(Builder|T): Builder|\Illuminate\Database\Query\Expression|array|string $column
     *
     * @return $this
     *
     * @see \Illuminate\Database\Eloquent\Builder::where()
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and'): self
    {
        return $this->using(fn(Builder $q): Builder => $q->where($column, $operator, $value, $boolean));
    }

    /**
     * Set a "where not" constraint on the query.
     *
     * @param \Closure(Builder|T): Builder|\Illuminate\Database\Query\Expression|array|string $column
     *
     * @return $this
     *
     * @see \Illuminate\Database\Eloquent\Builder::where()
     */
    public function whereNot($column, $operator = null, $value = null, $boolean = 'and'): self
    {
        return $this->using(fn(Builder $q): Builder => $q->whereNot($column, $operator, $value, $boolean));
    }

    /**
     * Set a "where null" constraint on the query.
     *
     * @param string $column
     *
     * @return $this
     */
    public function whereNull(string $column): self
    {
        return $this->using(fn(Builder $q): Builder => $q->whereNull($column));
    }

    /**
     * Set a "where not null" constraint on the query.
     *
     * @param string $column
     *
     * @return $this
     */
    public function whereNotNull(string $column): self
    {
        return $this->using(fn(Builder $q): Builder => $q->whereNotNull($column));
    }

    /**
     * Set a "where in" constraint on the query.
     *
     * @param string $column
     * @param \Illuminate\Contracts\Support\Arrayable|array $values
     *
     * @return $this
     */
    public function whereIn(string $column, $values): self
    {
        return $this->using(fn(Builder $q): Builder => $q->whereIn($column, $values));
    }

    /**
     * Set a "where not in" constraint on the query.
     *
     * @param string $column
     * @param \Illuminate\Contracts\Support\Arrayable|array $values
     *
     * @return $this
     */
    public function whereNotIn(string $column, $values): self
    {
        return $this->using(fn(Builder $q): Builder => $q->whereNotIn($column, $values));
    }

    /**
     * Ignore soft deleted models during the existence check.
     *
     * @return $this
     */
    public function withoutTrashed(): self
    {
        $this->using(function (Builder $query): Builder {
            /** @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\SoftDeletes|T $query */
            return $query->withoutTrashed();
        });

        return $this;
    }

    /**
     * Only include soft deleted models during the existence check.
     *
     * @return $this
     */
    public function onlyTrashed(): self
    {
        $this->using(function (Builder $query): Builder {
            /** @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\SoftDeletes|T $query */
            return $query->withTrashed();
        });

        return $this;
    }

    /**
     * Register a custom query callback.
     *
     * @param \Closure(Builder|T): Builder $callback
     *
     * @return $this
     */
    public function using(Closure $callback): self
    {
        $this->using[] = $callback;

        return $this;
    }

    /**
     * @param \Closure(T|\Illuminate\Support\Collection<T>): null $modelSetter
     *
     * @return self
     */
    public function withResultSetter(Closure $modelSetter): self
    {
        $this->resultSetter = $modelSetter;

        return $this;
    }

    /**
     * @template M of \Illuminate\Database\Eloquent\Model
     *
     * @param class-string<M> $modelClass
     * @param string|null $column
     *
     * @return self
     */
    public static function make(string $modelClass, ?string $column = null): self
    {
        return new self($modelClass, $column);
    }
}
