<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Competition\Theme;
use App\Models\Lead;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

/**
 * Class ThemeResource
 * @package App\Http\Resources
 *
 * @property-read \App\Models\Competition\Theme $resource
 */
#[OA\Schema(
    schema: 'ThemeResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'int', nullable: false),
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'description', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: false),
        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'tile', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'tile_size', ref: '#/components/schemas/TileSizeField', type: 'integer', nullable: false),
        new OA\Property(property: 'leads', type: 'array', nullable: false, items: new OA\Items(ref: '#/components/schemas/CompetitionLeadResource')),
    ],
)]
final class ThemeResource extends JsonResource
{
    /**
     * ThemeResource constructor.
     *
     * @param \App\Models\Competition\Theme $resource
     * @param \Illuminate\Support\Collection<\App\Models\MasterClass\MasterClass<\App\Models\Competition\CompetitionMasterClass>>|null $masterClasses
     */
    public function __construct(
        Theme $resource,
        private readonly ?Collection $masterClasses = null,
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
            'description' => $this->resource->description,
            'tile_size' => $this->resource->tile_size?->value,
            'tile' => $this->resource->tile,
            'image' => $this->resource->cover,
            'leads' => $this->resource->leads->map(fn(Lead $lead): array => [
                'name' => $lead->name,
                'description' => $lead->description,
                'image' => $lead->photo,
            ])->all(),
            'master_classes' => $this->when(null !== $this->masterClasses, function () use (
                $request
            ): array {
                return CompetitionMasterClassResource::collection($this->masterClasses)->toArray($request);
            }),
        ];
    }
}
