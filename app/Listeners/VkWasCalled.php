<?php

declare(strict_types = 1);

namespace App\Listeners;

use App\Providers\VkServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class VkWasCalled
{
    /**
     * Register the provider.
     *
     * @param \SocialiteProviders\Manager\SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled): void
    {
        $socialiteWasCalled->extendSocialite('vkontakte', VkServiceProvider::class);
    }

}
