<?php

declare(strict_types=1);

namespace App\Enums\Traits;

/**
 * EnumToArray trait
 *
 * @template T of int|string
 */
trait EnumToArray
{
    /**
     * @return string[]
     */
    public static function names(): array
    {
        return \array_column(self::cases(), 'name');
    }

    /**
     * @return T[]
     */
    public static function values(): array
    {
        return \array_column(self::cases(), 'value');
    }

    /**
     * @return T[]
     */
    public static function labels(): array
    {
        $labels = [];
        foreach (self::cases() as $self) {
            $labels[$self->value] = $self->label();
        }
        return $labels;
    }

    /**
     * @return array<T, string>
     */
    public static function array(): array
    {
        return \array_combine(self::values(), self::names());
    }
}
