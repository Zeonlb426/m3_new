<?php

declare(strict_types=1);

namespace App\Enums\Competition;

use App\Enums\Traits\EnumToArray;

/**
 * Class WorkContentType
 * @package App\Enums\Competition
 */
enum WorkContentType: string
{
    use EnumToArray;

    case AUDIO = 'audio';
    case VIDEO = 'video';
    case TEXT = 'text';
    case IMAGE = 'image';
    case IMAGES = 'images';
}
