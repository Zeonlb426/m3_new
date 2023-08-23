<?php

declare(strict_types=1);

namespace App\Http\Resources\News;

use App\Http\Resources\LikeResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class NewsResource
 * @package App\Http\News\News
 *
 * @property-read \App\Models\News\News $resource
 */
#[OA\Schema(
    schema: 'NewsResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'slug', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'announce', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'publish_date', type: 'string', maxLength: 255, example: '2022-02-24 03:30:00', nullable: false),
        new OA\Property(property: 'images', ref: '#/components/schemas/ImagesResource', type: 'object', nullable: false),
        new OA\Property(property: 'likes', ref: '#/components/schemas/LikeResource', type: 'object', nullable: false),
    ],
)]
final class NewsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'announce' => $this->resource->announce,
            'publish_date' => $this->resource->publish_date?->format('Y-m-d H:i:s'),
            'images' => \array_filter([
                'original' => $this->resource->cover,
                'thumbnail' => $this->resource->thumb,
            ]),
            'likes' => LikeResource::make($this->resource)->toArray($request),
        ];
    }
}
