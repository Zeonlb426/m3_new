<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use App\Http\Resources\Location\CityResource;
use App\Http\Resources\Location\RegionResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class SelfUserResource
 * @package App\Http\Resources\User
 *
 * @property-read User $resource
 */
#[OA\Schema(
    schema: 'SelfUserResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'first_name', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'last_name', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'avatar', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'email', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'phone', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'birth_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'region', ref: '#/components/schemas/RegionResource', type: 'object', nullable: true),
        new OA\Property(property: 'city', ref: '#/components/schemas/CityResource', type: 'object', nullable: true),
        new OA\Property(property: 'has_password', type: 'bool', nullable: false),
        new OA\Property(property: 'created_at', type: 'string', format: 'date', nullable: true),
    ],
)]
final class SelfUserResource extends JsonResource
{
    public function toArray($request): array
    {
        $this->resource->loadMissing(['city', 'region']);

        return [
            'id' => $this->resource->id,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'avatar' => $this->resource->avatar ?: null,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'birth_date' => $this->resource->birth_date?->format('Y-m-d'),
            'region' => isset($this->resource->region) ? RegionResource::make($this->resource->region) : null,
            'city' => isset($this->resource->city) ? CityResource::make($this->resource->city) : null,
            'has_password' => null !== $this->resource->password,
            'created_at' => $this->resource->created_at?->format('Y-m-d'),
        ];
    }
}
