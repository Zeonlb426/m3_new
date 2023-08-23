<?php

declare(strict_types=1);

namespace App\Enums\MasterClass;

use App\Enums\Traits\EnumToArray;
use OpenApi\Attributes as OA;

/**
 * Class AdditionalSign
 * @package App\Enums\MasterClass
 */
#[OA\Schema(
    schema: 'AdditionalMarkField',
    description: <<<STR
Типы меток:
<li>general - На главной</li>
STR,
    type: 'string',
    enum: [
        self::GENERAL,
    ]
)]
enum AdditionalSign: string
{
    use EnumToArray;

    case GENERAL = 'general';

    public function label(): string
    {
        return match ($this) {
            self::GENERAL => \__('enum.models.master_classes.additional_markups.general'),
        };
    }
}
