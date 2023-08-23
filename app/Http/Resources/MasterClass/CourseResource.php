<?php

declare(strict_types = 1);

namespace App\Http\Resources\MasterClass;

use App\Http\Resources\LeadNameResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class CourseResource
 * @package App\Http\Resources\MasterClass
 *
 * @property-read \App\Models\MasterClass\Course $resource
 */
#[OA\Schema(
    schema: 'CourseResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'name', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'slug', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'description', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: true),
        new OA\Property(property: 'leads', ref: '#/components/schemas/LeadNameResource', type: 'object', nullable: true),
    ],
    type: 'object',
)]
final class CourseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'leads' => LeadNameResource::collection($this->resource->leads)->toArray($request),
        ];
    }
}
