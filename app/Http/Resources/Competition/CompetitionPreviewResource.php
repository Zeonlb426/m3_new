<?php

declare(strict_types=1);

namespace App\Http\Resources\Competition;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class CompetitionPreviewResource
 * @package App\Http\Resources\Competition
 *
 * @property-read \App\Models\Competition\Competition $resource
 */
#[OA\Schema(
    schema: 'CompetitionPreviewResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'slug', type: 'string', maxLength: 255, nullable: false),
    ],
)]
final class CompetitionPreviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
        ];
    }
}
