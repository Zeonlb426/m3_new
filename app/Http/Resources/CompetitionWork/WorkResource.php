<?php

declare(strict_types=1);

namespace App\Http\Resources\CompetitionWork;

use App\Http\Resources\LikeResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class WorkResource
 * @package App\Http\Resources\CompetitionWork
 *
 * @property-read \App\Models\CompetitionWork\Work $resource
 */
#[OA\Schema(
    schema: 'WorkResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'author', type: 'object', nullable: false, properties: [
            new OA\Property(property: 'name', type: 'string', maxLength: 255, nullable: false),
            new OA\Property(property: 'age', type: 'number', nullable: false),
        ]),
        new OA\Property(property: 'work_type', type: 'object', nullable: false, properties: [
            new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
            new OA\Property(property: 'slug', type: 'string', maxLength: 255, nullable: true),
            new OA\Property(property: 'ext', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
        ]),
        new OA\Property(property: 'content', type: 'object', nullable: false, oneOf: [
            new OA\Schema(properties: [
                new OA\Property(property: 'audio', type: 'string', maxLength: 255, nullable: false),
            ]),
            new OA\Schema(properties: [
                new OA\Property(property: 'video', ref: '#/components/schemas/SocialVideoResource'),
            ]),
            new OA\Schema(properties: [
                new OA\Property(property: 'video', ref: '#/components/schemas/SocialVideoResource'),
                new OA\Property(property: 'text', type: 'string', maxLength: 255, nullable: false),
            ]),
            new OA\Schema(properties: [
                new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: false),
            ]),
            new OA\Schema(properties: [
                new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: false),
                new OA\Property(property: 'text', type: 'string', maxLength: 255, nullable: false),
            ]),
            new OA\Schema(properties: [
                new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string'), nullable: false),
            ]),
            new OA\Schema(properties: [
                new OA\Property(property: 'text', type: 'string', maxLength: 255, nullable: false),
            ]),
        ],
        ),
        new OA\Property(property: 'preview', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'likes', ref: '#/components/schemas/LikeResource', type: 'object', nullable: false),
    ],
)]
final class WorkResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'author' => [
                'name' => $this->resource->author->name,
                'age' => $this->resource->author->birth_date?->age,
            ],
            'work_type' => [
                'title' => $this->resource->workType->title,
                'slug' => $this->resource->workType->slug,
                'ext' => $this->resource->workType->formats
            ],
            'content' => $this->resource->content,
            'preview' => $this->resource->preview_url,
            'likes' => LikeResource::make($this->resource)->toArray($request),
        ];
    }
}
