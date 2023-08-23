<?php

declare(strict_types=1);

namespace App\Models\Objects\Competition;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class SectionEnabled extends Data
{
    public function __construct(
        public readonly bool $lead = false,
        public readonly bool $theme = false,
        public readonly bool $partner = false,
        #[MapName('master-class')]
        public readonly bool $masterClass = false,
    ) {
    }
}
