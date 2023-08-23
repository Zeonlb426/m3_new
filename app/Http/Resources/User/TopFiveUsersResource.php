<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

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
    schema: 'TopFiveUsersResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'first_name', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'last_name', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'credits', type: 'integer', nullable: true),
        new OA\Property(property: 'avatar', type: 'string', maxLength: 255, nullable: true),
    ],
    type: 'object',
)]
final class TopFiveUsersResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'credits' => $this->resource->count_total,
            'avatar' => $this->resource->avatar,
        ];
    }
}
