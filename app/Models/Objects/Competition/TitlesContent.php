<?php

declare(strict_types=1);

namespace App\Models\Objects\Competition;

use OpenApi\Attributes as OA;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

#[OA\Schema(
    schema: 'CompetitionTitlesContent',
    type: 'object',
    properties: [
        new OA\Property(property: 'add_work_enabled', type: 'bool', nullable: false),
        new OA\Property(property: 'add_work_text', type: 'string', nullable: true),
        new OA\Property(property: 'works_enabled', type: 'bool', nullable: false),
        new OA\Property(property: 'works_filtration_enabled', type: 'bool', nullable: false),
        new OA\Property(property: 'like_text', type: 'string', nullable: true),
    ],
)]
final class TitlesContent extends Data
{
    public function __construct(
        #[MapName('section_name')]
        public readonly SectionName $sectionName = new SectionName(),
        #[MapName('section_enabled')]
        public readonly SectionEnabled $sectionEnabled = new SectionEnabled(),
        #[MapName('themes_enabled')]
        public readonly bool $themesEnabled = false,
        #[MapName('add_work_enabled')]
        public readonly bool $addWorkEnabled = false,
        #[MapName('add_work_text')]
        public readonly ?string $addWorkText = null,
        #[MapName('works_enabled')]
        public readonly bool $worksEnabled = false,
        #[MapName('works_filtration_enabled')]
        public readonly bool $worksFiltrationEnabled = false,
        #[MapName('like_text')]
        public readonly ?string $likeText = null,
    ) {
    }
}
