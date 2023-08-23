<?php

declare(strict_types = 1);

namespace App\Enums\CompetitionWork;

use App\Enums\Traits\EnumToArray;

enum ApproveStatus: int
{
    use EnumToArray;

    case PENDING = 0;
    case APPROVED = 1;
    case REJECTED = 2;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => \__('enum.approve_status.pending'),
            self::APPROVED => \__('enum.approve_status.approved'),
            self::REJECTED => \__('enum.approve_status.rejected'),
        };
    }
}
