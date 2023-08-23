<?php

declare(strict_types=1);

namespace App\Contracts\Objects;

use App\Enums\SocialVideoType;
use Stringable;

/**
 * Interface SocialVideoLinkInterface
 * @package App\Contracts\Objects
 */
interface SocialVideoLinkInterface extends Stringable
{
    public function getVideoType(): SocialVideoType;

    public function getVideoId(): string;
}
