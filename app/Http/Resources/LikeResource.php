<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class LikeResource
 * @package App\Http\Resources
 *
 * @property-read \App\Contracts\Activities\HasLikesActivityInterface $resource
 */
#[OA\Schema(
    schema: 'LikeResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'total', type: 'integer', nullable: false),
        new OA\Property(property: 'is_liked', type: 'boolean', nullable: true),
    ],
)]
final class LikeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'total' => $this->resource->getLikesCount(),
            'is_liked' => \Auth::check() && $this->resource->likes->isNotEmpty(),
        ];
    }
}
