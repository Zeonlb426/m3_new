<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Promo;

use App\Http\Controllers\Controller;
use App\Http\Resources\Promo\SuccessHistoryResource;
use App\Http\Resources\Promo\SuccessHistoryViewResource;
use App\Repositories\Promo\SuccessHistoryRepository;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class SuccessHistoryController extends Controller
{
    public function __construct(
        private readonly SuccessHistoryRepository $histories
    ) {
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    #[OA\Get(
        path: '/api/v1/success-histories',
        summary: 'Получить список историй успеха',
        security: [],
        tags: ['Истории успеха'],
        parameters: [
            new OA\Parameter(name: 'offset', ref: '#/components/parameters/Offset'),
            new OA\Parameter(name: 'limit', ref: '#/components/parameters/Limit'),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Истории успеха',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/SuccessHistoryResource'),
                        ),
                        new OA\Property(
                            property: 'links',
                            ref: '#/components/schemas/LinksPaginatedResource',
                            type: 'object',
                        ),
                        new OA\Property(
                            property: 'meta',
                            ref: '#/components/schemas/MetaPaginatedResource',
                            type: 'object',
                        ),
                    ]
                ),
            ),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        return SuccessHistoryResource::collection(
            $this->histories->allPaginated((int)\request('limit'), \Auth::user())
        );
    }

    /**
     * @param string $id
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    #[OA\Get(
        path: '/api/v1/success-histories/{id}',
        summary: 'Получить историю успеха',
        security: [],
        tags: ['Истории успеха'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Объект истории успеха',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessHistoryViewResource'),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.not_found', response: Response::HTTP_NOT_FOUND),
        ],
    )]
    public function view(int $id): JsonResource
    {
        return SuccessHistoryViewResource::make(
            $this->histories->createApiQuery(authUser: \Auth::user())->with('sharing')->findOrFail($id)
        );
    }

    /**
     * @param $id
     * @param \App\Services\ActivityService $activityService
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    #[OA\Post(
        path: '/api/v1/success-histories/{id}/like',
        summary: 'Поставить/убрать лайк',
        security: [
            ['bearer' => []],
        ],
        tags: ['Истории успеха'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Состояние лайка',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'is_liked', type: 'bool'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.unauthorized', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(ref: '#/components/responses/error.not_found', response: Response::HTTP_NOT_FOUND),
        ],
    )]
    public function like($id, ActivityService $activityService): JsonResponse
    {
        return \response()->json([
            'data' => [
                'is_liked' => $activityService->triggerLikeAsync(
                    \Auth::user(),
                    $this->histories->createApiQuery(false)->findOrFail($id)
                ),
            ]
        ]);
    }
}
