<?php

declare(strict_types=1);

namespace App\Enums\User;

use App\Enums\Traits\EnumToArray;
use OpenApi\Attributes as OA;

/**
 * Class SocialProvider
 * @package App\Enums\User
 */
#[OA\Schema(
    schema: 'SocialProviderField',
    description: <<<STR
Типы провайдеров:
<li>vkontakte - Провайдер ВК</li>
STR,
    type: 'string',
    enum: [
        self::VK,
    ]
)]
enum SocialProvider: string
{
    use EnumToArray;

    // IMPORTANT!
    // Do not rename constant values!
    // They appear in the callbacks of social networks' applications 
    // Also using as names of Laravel Socialite providers.
    case VK = 'vkontakte';
}
