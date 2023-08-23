<?php

declare(strict_types=1);

namespace App\Http\Resources\MasterClass;

use App\Http\Resources\AgeGroupResource;
use App\Http\Resources\Competition\CompetitionPreviewResource;
use App\Http\Resources\LeadNameResource;
use App\Http\Resources\LikeResource;
use App\Http\Resources\SharingResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class MasterClassResource
 * @package App\Http\Resources\MasterClass
 *
 * @property-read \App\Models\MasterClass\MasterClass $resource
 */
#[OA\Schema(
    schema: 'MasterClassViewResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'video', ref: '#/components/schemas/SocialVideoResource'),
        new OA\Property(property: 'marks', type: 'array', items: new OA\Items(ref: '#/components/schemas/AdditionalMarkField'), nullable: false),
        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'content', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: true),
        new OA\Property(property: 'lead', nullable: true, ref: '#/components/schemas/LeadNameResource'),
        new OA\Property(property: 'age_group', ref: '#/components/schemas/AgeGroupResource', type: 'object', nullable: true),
        new OA\Property(property: 'likes', ref: '#/components/schemas/LikeResource', type: 'object', nullable: false),
        new OA\Property(property: 'sharing', ref: '#/components/schemas/SharingResource', type: 'object', nullable: true),
        new OA\Property(property: 'competitions', type: 'array', nullable: false, items: new OA\Items(
            ref: '#/components/schemas/CompetitionPreviewResource',
        )),
    ],
)]
final class MasterClassViewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'video' => $this->resource->video,
            'marks' => \array_column($this->resource->signs, 'value'),
            'image' => $this->resource->image,
            'content' => $this->resource->content,
            'lead' => $this->resource->lead_id
                ? LeadNameResource::make($this->resource->lead)->toArray($request)
                : null,
            'age_group' => $this->resource->age_group_id
                ? AgeGroupResource::make($this->resource->ageGroup)->toArray($request)
                : null,
            'likes' => LikeResource::make($this->resource)->toArray($request),
            'sharing' => $this->resource->sharing
                ? SharingResource::make($this->resource->sharing)->toArray($request)
                : null,
            'competitions' => $this->resource->competitions->isNotEmpty()
                ? CompetitionPreviewResource::collection($this->resource->competitions)
                : null,
        ];
    }
}
