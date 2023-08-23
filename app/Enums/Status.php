<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Traits\EnumToArray;

enum Status: int
{
    /**
     * @use \App\Enums\Traits\EnumToArray<int>
     */
    use EnumToArray;

    case NEW = 0;
    case SUCCESS = 1;
    case FAILURE = 2;

    public function label(): array
    {
        return match ($this) {
            self::NEW => \__('enum.status.new'),
            self::SUCCESS => \__('enum.status.success'),
            self::FAILURE => \__('enum.status.failure'),
        };
    }
}
