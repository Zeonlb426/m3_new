<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Competition\CompetitionPreviewResource;
use App\Models\Competition\Competition;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class CompetitionMasterClassResource
 * @package App\Http\Resources
 *
 * @property-read \App\Models\MasterClass\MasterClass<\App\Models\Competition\CompetitionMasterClass> $resource
 */
#[OA\Schema(
    schema: 'CompetitionMasterClassResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: true),
        new OA\Property(property: 'is_main', type: 'boolean', nullable: true),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'content', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'lead', nullable: true, ref: '#/components/schemas/LeadNameResource'),
        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'video', ref: '#/components/schemas/SocialVideoResource'),
        new OA\Property(property: 'likes', ref: '#/components/schemas/LikeResource'),
        new OA\Property(
            property: 'competitions',
            type: 'array',
            nullable: true,
            items: new OA\Items(ref: '#/components/schemas/CompetitionPreviewResource'),
        ),
    ],
)]
final class CompetitionMasterClassResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        $isMainMasterClass = $this->resource->pivot->is_main;

        return [
            'id' => $this->resource->id,
            'is_main' => $isMainMasterClass,
            'title' => $this->resource->title,
            'content' => $this->resource->content,
            'lead' => $this->resource->lead_id
                ? LeadNameResource::make($this->resource->lead)->toArray($request)
                : null,
            'image' => $this->resource->image,
            'video' => $this->resource->video,
            'likes' => LikeResource::make($this->resource)->toArray($request),
            'competitions' => $this->when($isMainMasterClass, function () use ($request): array {
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
