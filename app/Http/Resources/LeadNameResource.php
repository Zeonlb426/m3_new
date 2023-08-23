<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class LeadNameResource
 * @package App\Http\Resources\Location
 *
 * @property-read \App\Models\Lead $resource
 */
#[OA\Schema(
    schema: 'LeadNameResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'name', type: 'string', maxLength: 255, nullable: false),
    ],
    type: 'object',
)]
final class LeadNameResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
        ];
    }
}
