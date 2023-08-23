<?php

declare(strict_types = 1);

namespace App\Models\Traits;

/**
 * @property \Illuminate\Database\Eloquent\Relations\Pivot $pivot
 */
trait HasPivotJson
{
    public static function pivotAttributes(): ?array
    {
        return isset(static::$pivotAdditional) && \is_array(static::$pivotAdditional)
            ? \array_keys(static::$pivotAdditional)
            : null
        ;
    }

    public function pivotAttribute(?string $attribute = null, $default = null, ?array $initData = null): mixed
    {
        $data = $initData ?? static::$pivotAdditional ?? [];
        if (null === $attribute) {
            if (false === empty($data)) {
                $attributes = [];
                foreach ($data as $attribute => $castType) {
                    $attributes[$attribute] = $this->castPivot($attribute, $castType);
                }

                return $attributes;
            }
            return null;
        }

        return $this->castPivot($attribute, $data[$attribute]) ?: $default;
    }

    public function castPivot(string $attribute, string $castType): mixed
    {
        $cacheKey = \sprintf('%s_%s', \implode('_', \explode('\\', static::class)), $attribute);
        if (false === isset($this->casts[$cacheKey])) {
            $this->casts[$cacheKey] = $castType;
        }

        if (false === isset($this->pivot->{$attribute})) {
            return null;
        }

        if (\in_array($castType, ['int', 'bool', 'string'])) {
            return $this->pivot?->{$attribute} ?? null;
        }
        if (\in_array($castType, ['array', 'json'])) {
            return \json_decode($this->pivot?->{$attribute} ?? $this->{$attribute} ?? "null", true);
        }

        return $this->castAttribute(
            $attribute,
            $this->{$attribute} ?? null
        );
    }

    public function __get($key)
    {
        $arg = [];
        if (\is_string($key) && '_' === $key[0] && \preg_match('/^_pivot_(.*)$/i', $key, $arg)) {
            return $this->pivotAttribute(\array_pop($arg));
        }
        return $this->getAttribute($key);
    }
}
