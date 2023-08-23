<?php

declare(strict_types = 1);

namespace App\Enums\User;

use App\Enums\Traits\EnumToArray;

enum ActionType: int
{
    use EnumToArray;

    case REGISTRATION = 10;
    case LIKE = 20;
    case ADD_WORK = 30;

    public function label(): string
    {
        return match ($this) {
            self::REGISTRATION => \__('enum.models.action_type.registration'),
            self::LIKE => \__('enum.models.action_type.like'),
            self::ADD_WORK => \__('enum.models.action_type.add_work'),
        };
    }
}
