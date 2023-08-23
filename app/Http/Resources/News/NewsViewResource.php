<?php

declare(strict_types=1);

namespace App\Http\Resources\News;

use App\Http\Resources\LikeResource;
use App\Models\News\News;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

/**
 * Class NewsViewResource
 * @package App\Http\Resources\News
 *
 * @property-read \App\Models\News\News $resource
 */
#[OA\Schema(
    schema: 'NewsViewResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'slug', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'announce', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'video_link', nullable: true, ref: '#/components/schemas/SocialVideoResource'),
        new OA\Property(property: 'publish_date', type: 'string', maxLength: 255, example: '2022-02-24 03:30:00', nullable: false),
        new OA\Property(property: 'images', ref: '#/components/schemas/ImagesResource', type: 'object', nullable: false),
        new OA\Property(property: 'content', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: true),
        new OA\Property(property: 'likes', ref: '#/components/schemas/LikeResource', type: 'object', nullable: false),
        new OA\Property(
            property: 'related',
            type: 'array',
            items: new OA\Items(
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
            ),
        ),
    ],
)]
final class NewsViewResource extends JsonResource
{
    /**
     * NewsViewResource constructor.
     *
     * @param \App\Models\News\News $resource
     * @param \Illuminate\Support\Collection<\App\Models\News\News> $relatedNews
     */
    public function __construct(
        $resource,
        private readonly Collection $relatedNews,
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'announce' => $this->resource->announce,
            'video' => $this->resource->video,
            'publish_date' => $this->resource->publish_date?->format('Y-m-d H:i:s'),
            'images' => \array_filter([
                'original' => $this->resource->cover,
                'thumbnail' => $this->resource->thumb,
            ]),
            'content' => $this->resource->content,
            'likes' => LikeResource::make($this->resource)->toArray($request),
            'related' => $this->relatedNews->map(fn(News $news): array => [
                'id' => $news->id,
                'title' => $news->title,
                'slug' => $news->slug,
                'announce' => $news->announce,
                'publish_date' => $news->publish_date?->format('Y-m-d H:i:s'),
                'images' => \array_filter([
                    'original' => $news->cover,
                    'thumbnail' => $news->thumb,
                ]),
                'likes' => LikeResource::make($news)->toArray($request),
            ]),
        ];
    }
}
