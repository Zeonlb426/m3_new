<?php

declare(strict_types=1);

namespace App\Http\Resources\Location;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class RegionResource
 * @package App\Http\Resources\Location
 *
 * @property-read \App\Models\Location\Region $resource
 */
#[OA\Schema(
    schema: 'RegionResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: true),
    ],
    type: 'object',
)]
final class RegionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
        ];
    }
}
