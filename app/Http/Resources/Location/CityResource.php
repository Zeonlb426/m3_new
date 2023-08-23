<?php

declare(strict_types=1);

namespace App\Http\Resources\Location;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class CityResource
 * @package App\Http\Resources\Location
 *
 * @property-read \App\Models\Location\City $resource
 */
#[OA\Schema(
    schema: 'CityResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'region_id', type: 'integer', nullable: false),
    ],
    type: 'object',
)]
final class CityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'region_id' => $this->resource->region_id,
        ];
    }
}
