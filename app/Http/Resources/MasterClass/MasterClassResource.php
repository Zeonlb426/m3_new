<?php

declare(strict_types=1);

namespace App\Http\Resources\MasterClass;

use App\Http\Resources\Competition\CompetitionPreviewResource;
use App\Http\Resources\LeadNameResource;
use App\Http\Resources\LikeResource;
use App\Models\Competition\Competition;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class MasterClassResource
 * @package App\Http\Resources\MasterClass
 *
 * @property-read \App\Models\MasterClass\MasterClass $resource
 */
#[OA\Schema(
    schema: 'MasterClassResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'content', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: true),
        new OA\Property(property: 'lead', nullable: true, ref: '#/components/schemas/LeadNameResource'),
        new OA\Property(property: 'video', ref: '#/components/schemas/SocialVideoResource'),
        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'likes', ref: '#/components/schemas/LikeResource'),
        new OA\Property(
            property: 'competitions',
            type: 'array',
            nullable: true,
            items: new OA\Items(ref: '#/components/schemas/CompetitionPreviewResource'),
        ),
    ],
)]
final class MasterClassResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'content' => $this->resource->content,
            'lead' => $this->resource->lead_id
                ? LeadNameResource::make($this->resource->lead)->toArray($request)
                : null,
            'video' => $this->resource->video,
            'image' => $this->resource->image,
            'likes' => LikeResource::make($this->resource)->toArray($request),
            'competitions' => $this->whenLoaded('competitionsPreviews', function () use ($request): array {
                return $this->resource->competitionsPreviews
                    ->map(
                        fn(Competition $competition): array => (new CompetitionPreviewResource($competition))->toArray($request)
                    )
                    ->values()
                    ->all()
                ;
            }),
        ];
    }
}
