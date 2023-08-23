<?php

declare(strict_types=1);

namespace App\Providers;

use SocialiteProviders\VKontakte\Provider;

final class VkServiceProvider extends Provider
{
    protected $fields = [
        'id', 'email', 'first_name', 'last_name', 'sex', 'country', 'city', 'bdate',
        'screen_name', 'photo_200',
    ];
}
