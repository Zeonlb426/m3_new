<?php

declare(strict_types = 1);

namespace App\Http\Resources\Competition;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class CompetitionSearchResource
 * @package App\Http\Resources\Competition
 *
 * @property-read \App\Models\Competition\Competition $resource
 */
#[OA\Schema(
    schema: 'CompetitionSearchResource',
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'slug', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'content', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: false),
    ],
    type: 'object',
)]
final class CompetitionSearchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'content' => $this->resource->short_content,
        ];
    }
}
