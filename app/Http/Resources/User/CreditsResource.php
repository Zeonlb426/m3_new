<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Class CreditsResource
 * @package App\Http\Resources\User
 *
 * @property-read \App\Models\User\UserTotalCredit $resource
 */
#[OA\Schema(
    schema: 'CreditsResource',
    properties: [
        new OA\Property(property: 'total', description: 'Общая сумма кредитов', type: 'integer', nullable: false),
        new OA\Property(property: 'register', description: 'Сумма кредитов за регистрацию', type: 'integer', nullable: false),
        new OA\Property(property: 'likes', description: 'Сумма кредитов за лайки', type: 'integer', nullable: false),
        new OA\Property(property: 'works', description: 'Сумма кредитов за выложенные работы', type: 'integer', nullable: false),
    ],
    type: 'object',
)]
final class CreditsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'total' => $this->resource->count_total ?: 0,
            'register' => $this->resource->count_register ?: 0,
            'likes' => $this->resource->count_likes ?: 0,
            'works' => $this->resource->count_works ?: 0,
        ];
    }
}
