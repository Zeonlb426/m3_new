<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class TokenResource
 * @package App\Http\Resources\Auth
 *
 * @property-read \Laravel\Sanctum\NewAccessToken $resource
 */
#[OA\Schema(
    schema: 'TokenResource',
    properties: [
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'token', type: 'string'),
    ],
    type: 'object',
)]
class TokenResource extends JsonResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'type' => 'Bearer',
            'token' => $this->resource->plainTextToken,
        ];
    }
}
