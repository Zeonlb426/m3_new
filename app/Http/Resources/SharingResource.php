<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class SharingResource
 * @package App\Http\Resources
 *
 * @property-read \App\Models\Sharing $resource
 */
#[OA\Schema(
    schema: 'SharingResource',
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'description', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: false),
        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: false),
    ],
    type: 'object',
)]
final class SharingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'image' => $this->resource->image,
        ];
    }
}
