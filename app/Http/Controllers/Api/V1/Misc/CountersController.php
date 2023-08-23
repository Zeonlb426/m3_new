<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Misc;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\TopFiveUsersResource;
use App\Models\User\UserTotalCredit;
use App\Repositories\User\UserActivityRepository;
use App\Settings\CountersSettings;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class CountersController extends Controller
{
    /**
     * @param \App\Settings\CountersSettings $settings
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/counters',
        summary: 'Получить счётчики',
        security: [],
        tags: ['Служебные'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Счётчики',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'likes_total', type: 'integer'),
                                new OA\Property(property: 'credits_total', type: 'integer'),
                                new OA\Property(
                                    property: 'top',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/TopFiveUsersResource')
                                ),
                            ],
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function __invoke(CountersSettings $settings): JsonResponse
    {
        return \response()->json([
            'data' => [
                'likes_total' => UserActivityRepository::getActivitiesCountQuery()->count() + (int)$settings->fake_likes,
                'credits_total' => UserTotalCredit::query()->sum('count_total') + (int)$settings->fake_credits,
                'top' => TopFiveUsersResource::collection(UserActivityRepository::getTopFiveUsers())
            ],
        ]);
    }
}
