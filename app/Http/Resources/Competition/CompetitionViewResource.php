<?php

declare(strict_types=1);

namespace App\Http\Resources\Competition;

use App\Http\Resources\CompetitionMasterClassResource;
use App\Http\Resources\ThemeResource;
use App\Models\AgeGroup;
use App\Models\Competition\Prize;
use App\Models\Competition\Theme;
use App\Models\Competition\WorkType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

/**
 * Class CompetitionViewResource
 * @package App\Http\Resources\Competition
 *
 * @property-read \App\Models\Competition\Competition $resource
 */
#[OA\Schema(
    schema: 'TitleTextsField',
    properties: [
        new OA\Property(property: 'section_name', type: 'string', maxLength: 255, nullable: true),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'PartnerTitleTextsField',
    properties: [
        new OA\Property(property: 'section_name', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'partner_text', type: 'string', maxLength: 255, nullable: true),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'CompetitionLeadResource',
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'description', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: false),
        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: true),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'CompetitionViewResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'period', type: 'string', maxLength: 255, nullable: true),
        new OA\Property(property: 'slug', type: 'string', maxLength: 255, nullable: false),
        new OA\Property(property: 'content', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: false),
        new OA\Property(property: 'title_texts', ref: '#/components/schemas/CompetitionTitlesContent', type: 'object', nullable: true),
        new OA\Property(property: 'work_types', type: 'array', items: new OA\Items(
            properties: [
                new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: false),
                new OA\Property(property: 'slug', type: 'string', maxLength: 255, nullable: true),
                new OA\Property(property: 'ext', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
            ],
            type: 'object'
        ), nullable: false),
        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: false),

        new OA\Property(property: 'blocks', properties: [
            new OA\Property(property: 'themes', properties: [
                new OA\Property(property: 'title_texts', ref: '#/components/schemas/TitleTextsField', type: 'object', nullable: true),
                new OA\Property(property: 'section_enabled', type: 'bool', nullable: false),
                new OA\Property(
                    property: 'data', type: 'array', nullable: false, items: new OA\Items(ref: '#/components/schemas/ThemeResource')),
            ], type: 'object', nullable: true),

            new OA\Property(property: 'prizes', properties: [
                new OA\Property(property: 'info', properties: [
                    new OA\Property(property: 'like_text', type: 'string', maxLength: 255, nullable: true),
                    new OA\Property(property: 'gift_text', type: 'string', maxLength: 255, nullable: true),
                ], type: 'object', nullable: true),

                new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: true),
                        new OA\Property(property: 'description', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: false),
                        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: true),
                        new OA\Property(property: 'win_position', type: 'integer', nullable: true),
                        new OA\Property(property: 'link', type: 'string', maxLength: 255, nullable: true),
                    ],
                    type: 'object'
                ), nullable: false),
            ], type: 'object', nullable: true),

            new OA\Property(property: 'leads', properties: [
                new OA\Property(property: 'title_texts', ref: '#/components/schemas/TitleTextsField', type: 'object', nullable: true),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/CompetitionLeadResource'), nullable: false),
            ], type: 'object', nullable: true),

            new OA\Property(property: 'master_classes', properties: [
                new OA\Property(property: 'title_texts', ref: '#/components/schemas/TitleTextsField', type: 'object', nullable: true),
                new OA\Property(property: 'data', type: 'array', nullable: false, items: new OA\Items(
                    ref: '#/components/schemas/CompetitionMasterClassResource'
                )),
            ], type: 'object', nullable: true),

            new OA\Property(property: 'partners', properties: [
                new OA\Property(property: 'title_texts', ref: '#/components/schemas/TitleTextsField', type: 'object', nullable: true),
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'is_main', type: 'boolean', nullable: true),
                        new OA\Property(property: 'title', type: 'string', maxLength: 255, nullable: true),
                        new OA\Property(property: 'description', type: 'string', maxLength: 2147483647, example: '<p><b>Hi!</b> <br> I am <i><u>HTML</u></i> content</p>', nullable: false),
                        new OA\Property(property: 'link', type: 'string', maxLength: 255, nullable: true),
                        new OA\Property(property: 'image', type: 'string', maxLength: 255, nullable: true),
                        new OA\Property(property: 'bg', type: 'string', maxLength: 255, nullable: true),
                    ],
                    type: 'object',
                ), nullable: false),
            ], type: 'object', nullable: true),

        ], type: 'object', nullable: true),

        new OA\Property(property: 'filtration', properties: [
            new OA\Property(property: 'age_groups', type: 'array', items: new OA\Items(
                properties: [
                    new OA\Property(property: 'text', type: 'string', nullable: false),
                    new OA\Property(property: 'key', type: 'string', nullable: false),
                ],
                type: 'object'
            ), nullable: true),
            new OA\Property(property: 'themes', type: 'array', items: new OA\Items(
                properties: [
                    new OA\Property(property: 'text', type: 'string', nullable: false),
                    new OA\Property(property: 'key', type: 'string', nullable: false),
                ],
                type: 'object'
            ), nullable: true),
        ], type: 'object', nullable: true),
    ],
)]
final class CompetitionViewResource extends JsonResource
{
    public function toArray($request): array
    {
        $themes = $this->makeThemes($request);
        $prizes = $this->makePrizes();
        $leads = $this->makeLeads();
        $masterClasses = $this->makeMasterClasses($request);
        $partners = $this->makePartners();
        $filtration = $this->makeFiltration();

        return [
            'title' => $this->resource->title,
            'period' => $this->resource->period,
            'slug' => $this->resource->slug,
            'content' => $this->resource->content,
            'title_texts' => Arr::except(
                $this->resource->titles_content->toArray(), ['section_name', 'section_enabled']
            ),
            'work_types' => $this->resource->workTypes->map(fn(WorkType $workType) => [
                'title' => $workType->title,
                'slug' => $workType->slug,
                'ext' => $workType->formats
            ]),
            'image' => $this->resource->cover,
            'blocks' => [
                'themes' => $themes,
                'prizes' => $prizes,
                'leads' => $leads,
                'master_classes' => $masterClasses,
                'partners' => $partners,
            ],
            'filtration' => $filtration,
        ];
    }

    private function makeThemes(Request $request): array|MissingValue
    {
        if ($this->resource->titles_content->themesEnabled) {
            $themes = [
                'title_texts' => [
                    'section_name' => $this->resource->titles_content->sectionName->theme,
                ],
                'section_enabled' => $this->resource->titles_content->sectionEnabled->theme,
                'data' => $this->resource->themes->map(
                    fn(Theme $theme): array => (new ThemeResource($theme))->toArray($request)
                )->all(),
            ];
        } else {
            $themes = new MissingValue();
        }

        return $themes;
    }

    private function makePrizes(): array|MissingValue
    {
        $prizes = $this->resource->prizes;
        if ($prizes->isNotEmpty()) {

            $info = $this->resource->prizeInfo;
            if (null !== $info && null !== $info->titles_content) {
                $info = [
                    'like_text' => $info->titles_content['like_text'] ?? null,
                    'gift_text' => $info->titles_content['gift_text'] ?? null,
                ];
            } else {
                $info = new MissingValue();
            }
            $prizes = [
                'info' => $info,
                'data' => (new class ($prizes) extends JsonResource {
                    public function toArray($request): array
                    {
                        /* @var $res Prize */
                        $res = $this->resource;
                        return [
                            'title' => $res->title,
                            'description' => $res->description,
                            'image' => $res->image,
                            'win_position' => $res->win_position,
                            'link' => $res->link,
                        ];
                    }
                })::collection($prizes),
            ];
        } else {
            $prizes = new MissingValue();
        }

        return $prizes;
    }

    private function makeLeads(): array|MissingValue
    {
        $leads = $this->resource->leads;
        if ($this->resource->titles_content->sectionEnabled->lead) {
            $leads = [
                'title_texts' => [
                    'section_name' => $this->resource->titles_content->sectionName->lead,
                ],
                'data' => (new class ($leads) extends JsonResource {
                    public function toArray($request): array
                    {
                        /* @var $res \App\Models\Lead */
                        $res = $this->resource;
                        return [
                            'name' => $res->name,
                            'description' => $res->description,
                            'image' => $res->photo,
                        ];
                    }
                })::collection($leads),
            ];
        } else {
            $leads = new MissingValue();
        }

        return $leads;
    }

    private function makeMasterClasses(Request $request): array|MissingValue
    {
        if (false === $this->resource->titles_content->sectionEnabled->masterClass) {
            return new MissingValue();
        }

        $masterClasses = $this->resource->masterClasses->load(['lead', 'competitionsPreviews']);

        return [
            'title_texts' => [
                'section_name' => $this->resource->titles_content->sectionName->masterClass,
            ],
            'data' => CompetitionMasterClassResource::collection($masterClasses)->toArray($request),
        ];
    }

    private function makePartners(): array|MissingValue
    {
        $partners = $this->resource->partners;
        if ($this->resource->titles_content->sectionEnabled->partner) {
            $partners = [
                'title_texts' => [
                    'section_name' => $this->resource->titles_content->sectionName->partner,
                ],
                'data' => (new class ($partners) extends JsonResource {
                    public function toArray($request): array
                    {
                        /* @var $res \App\Models\Competition\Partner */
                        $res = $this->resource;

                        return [
                            'is_main' => (bool)$res->_pivot_is_main,
                            'title' => $res->title,
                            'description' => ($res->_pivot_titles_content ?: [])['partner_text'] ?? $res->description,
                            'link' => $res->link,
                            'image' => $res->logo,
                            'bg' => $res->background,
                        ];
                    }
                })::collection($partners),
            ];
        } else {
            $partners = new MissingValue();
        }

        return $partners;
    }

    private function makeFiltration(): ?array
    {
        if ($this->resource->titles_content->worksFiltrationEnabled &&
            $this->resource->themes->isNotEmpty() &&
            $this->resource->themes->count() > 1
        ) {
            $themes = $this->resource->themes->map(function (Theme $model) {
                return [
                    'text' => $model->title,
                    'key' => (string)$model->getKey(),
                ];
            })->toArray();
        }

        if ($this->resource->ageGroups->isNotEmpty() && $this->resource->ageGroups->count() > 1) {
            $ageGroups = $this->resource->ageGroups->map(function (AgeGroup $model) {
                return [
                    'text' => $model->title,
                    'key' => $model->slug,
                ];
            })->toArray();
        }

        return [
            'themes' => $themes ?? null,
            'age_groups' => $ageGroups ?? null,
        ];
    }
}
