<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class AgeGroupResource
 * @package App\Http\Resources
 *
 * @property-read \App\Models\AgeGroup $resource
 */
#[OA\Schema(
    schema: 'AgeGroupResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'slug', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'min_age', type: 'integer', nullable: false),
        new OA\Property(property: 'max_age', type: 'integer', nullable: false),
    ],
    type: 'object',
)]
final class AgeGroupResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->getKey(),
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'min_age' => $this->resource->min_age,
            'max_age' => $this->resource->max_age,
        ];
    }
}
