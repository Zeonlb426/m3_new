<?php

declare(strict_types=1);

namespace App\Models\Objects\Competition;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

final class SectionName extends Data
{
    public function __construct(
        public readonly ?string $lead = null,
        public readonly ?string $theme = null,
        public readonly ?string $partner = null,
        #[MapName('master-class')]
        public readonly ?string $masterClass = null,
    ) {
    }
}
