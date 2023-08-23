<?php

declare(strict_types=1);

namespace App\Enums\Misc;

use App\Enums\Traits\EnumToArray;

/**
 * Class ProcessingStatus
 * @package App\Enums\Misc
 */
enum ProcessingStatus: int
{
    use EnumToArray;

    case NEW = 0;
    case PROCESSED = 1;

    public function label(): string
    {
        return match ($this) {
            self::NEW => \__('enum.models.feedbacks.processing_status.new'),
            self::PROCESSED => \__('enum.models.feedbacks.processing_status.processed'),
        };
    }
}
