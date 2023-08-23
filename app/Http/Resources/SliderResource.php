<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class SliderResource
 * @package App\Http\Resources
 *
 * @property-read \App\Models\Slider $resource
 */
#[OA\Schema(
    schema: 'SliderResource',
    properties: [
        new OA\Property(property: 'short_title', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'link', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'description', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: true),
        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'image_mobile', type: 'string', maxLength: 255, nullable: true),
    ],
    type: 'object',
)]
final class SliderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'short_title' => $this->resource->short_title,
            'title' => $this->resource->title,
            'link' => $this->resource->link,
            'description' => $this->resource->description,
            'image' => $this->resource->image,
            'image_mobile' => $this->resource->image_mobile,
        ];
    }
}
