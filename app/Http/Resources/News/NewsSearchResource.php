<?php

declare(strict_types=1);

namespace App\Http\Resources\News;

use App\Http\Resources\LikeResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class NewsSearchResource
 * @package App\Http\News\News
 *
 * @property-read \App\Models\News\News $resource
 */
#[OA\Schema(
    schema: 'NewsSearchResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'slug', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'announce', type: 'string', maxLength: 255, nullable: true)
    ],
)]
final class NewsSearchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'announce' => $this->resource->announce,
        ];
    }
}
