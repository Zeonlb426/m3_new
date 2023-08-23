<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class LeadResource
 * @package App\Http\Resources\Location
 *
 * @property-read \App\Models\Lead $resource
 */
#[OA\Schema(
    schema: 'LeadResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'name', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'slug', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'short_description', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: true),
        new OA\Property(property: 'description', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: true),
        new OA\Property(property: 'photo', type: 'string', maxLength: 255, nullable: true),
    ],
    type: 'object',
)]
final class LeadResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'short_description' => $this->resource->short_description,
            'description' => $this->resource->description,
            'photo' => $this->resource->photo,
        ];
    }
}
