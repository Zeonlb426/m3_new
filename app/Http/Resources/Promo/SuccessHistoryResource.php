<?php

declare(strict_types=1);

namespace App\Http\Resources\Promo;

use App\Http\Resources\LikeResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class SuccessHistoryResource
 * @package App\Http\Promo\SuccessHistory
 *
 * @property-read \App\Models\Promo\SuccessHistory $resource
 */
#[OA\Schema(
    schema: 'SuccessHistoryResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'short_title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'short_description', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: true),
        new OA\Property(property: 'likes', ref: '#/components/schemas/LikeResource', type: 'object', nullable: false),
    ],
)]
final class SuccessHistoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'short_title' => $this->resource->short_title,
            'title' => $this->resource->title,
            'image' => $this->resource->image,
            'short_description' => $this->resource->short_description,
            'likes' => LikeResource::make($this->resource)->toArray($request),
        ];
    }
}
