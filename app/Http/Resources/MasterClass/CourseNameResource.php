<?php

declare(strict_types = 1);

namespace App\Http\Resources\MasterClass;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class CourseNameResource
 * @package App\Http\Resources\MasterClass
 *
 * @property-read \App\Models\MasterClass\Course $resource
 */
#[OA\Schema(
    schema: 'CourseNameResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'name', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'slug', type: 'string', maxLength: 255, nullable: false),
    ],
    type: 'object',
)]
final class CourseNameResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->name,
            'slug' => $this->resource->slug,
        ];
    }
}
