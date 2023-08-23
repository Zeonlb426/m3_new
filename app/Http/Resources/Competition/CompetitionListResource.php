<?php

declare(strict_types = 1);

namespace App\Http\Resources\Competition;

use App\Http\Resources\AgeGroupResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class CompetitionListResource
 * @package App\Http\Resources\Competition
 *
 * @property-read \App\Models\Competition\Competition $resource
 */
#[OA\Schema(
    schema: 'CompetitionListResource',
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'slug', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'content', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: false),
        new OA\Property(property: 'tile_size', ref: '#/components/schemas/TileSizeField', type: 'integer', nullable: false),
        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'age_groups', type: 'array', items: new OA\Items(
            ref: '#/components/schemas/AgeGroupResource',
            type: 'object'
        ), nullable: false),
    ],
    type: 'object',
)]
final class CompetitionListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'content' => $this->resource->short_content,
            'tile_size' => $this->resource->tile_size?->label(),
            'image' => $this->resource->tile,
            'age_groups' => AgeGroupResource::collection($this->resource->ageGroups)->toArray($request),
        ];
    }
}
