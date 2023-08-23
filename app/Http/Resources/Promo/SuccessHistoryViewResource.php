<?php

declare(strict_types=1);

namespace App\Http\Resources\Promo;

use App\Http\Resources\LikeResource;
use App\Http\Resources\SharingResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class SuccessHistoryResource
 * @package App\Http\Promo\SuccessHistory
 *
 * @property-read \App\Models\Promo\SuccessHistory $resource
 */
#[OA\Schema(
    schema: 'SuccessHistoryViewResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'short_title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'video', ref: '#/components/schemas/SocialVideoResource'),
        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'short_description', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: true),
        new OA\Property(property: 'description', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: true),
        new OA\Property(property: 'likes', ref: '#/components/schemas/LikeResource', type: 'object', nullable: false),
        new OA\Property(property: 'sharing', ref: '#/components/schemas/SharingResource', type: 'object', nullable: true),
        new OA\Property(property: 'next', ref: '#/components/schemas/NextSuccessHistoryResource', type: 'object', nullable: false),
    ],
)]
final class SuccessHistoryViewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'short_title' => $this->resource->short_title,
            'title' => $this->resource->title,
            'video' => $this->resource->video,
            'image' => $this->resource->image,
            'short_description' => $this->resource->short_description,
            'description' => $this->resource->description,
            'likes' => LikeResource::make($this->resource)->toArray($request),
            'sharing' => $this->resource->sharing
                ? SharingResource::make($this->resource->sharing)->toArray($request)
                : null,
            'next' => $this->next->only(['id', 'title', 'image']),
        ];
    }
}
