<?php

declare(strict_types=1);

namespace App\Http\Resources\Competition;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class PartnerResource
 * @package App\Http\Resources\Competition
 *
 * @property-read \App\Models\Competition\Partner $resource
 */
#[OA\Schema(
    schema: 'PartnerResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', nullable: false),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'description', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: true),
        new OA\Property(property: 'link', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: false),
    ],
    type: 'object',
)]
final class PartnerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'link' => $this->resource->link,
            'image' => $this->resource->slider,
        ];
    }
}
