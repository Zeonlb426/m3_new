<?php

declare(strict_types=1);

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SocialVideoTypeEnum',
    type: 'int',
    nullable: false,
    enum: [
        self::YOUTUBE,
        self::VK,
    ],
    description: <<<STR
<li>1 - youtube</li>
<li>2 - vk</li>
STR,
)]
enum SocialVideoType: int
{
    case YOUTUBE = 1;
    case VK = 2;
}
