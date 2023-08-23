<?php

declare(strict_types = 1);

namespace App\Enums\Competition;

use App\Enums\Traits\EnumToArray;
use OpenApi\Attributes as OA;

/**
 * Class TileSize
 * @package App\Enums\Competition
 */
#[OA\Schema(
    schema: 'TileSizeField',
    description: <<<STR
Виды плиток:
<li>1 - small 1x1 </li>
<li>2 - high - 2x2 </li>
<li>3 - wide 2x1 </li>
<li>4 - big 1x2 </li>
STR,
    type: 'string',
    enum: [
        self::SMALL,
        self::HIGH,
        self::WIDE,
        self::BIG,
    ]
)]
enum TileSize: int
{
    use EnumToArray;

    case SMALL = 1;
    case HIGH = 2;
    case WIDE = 3;
    case BIG = 4;

    public function label(): string
    {
        return match ($this) {
            self::SMALL => '1x1',
            self::HIGH => '2x2',
            self::WIDE => '2x1',
            self::BIG => '1x2',
        };
    }
}
