<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\News;

use App\Http\Controllers\Controller;
use App\Http\Resources\News\NewsResource;
use App\Http\Resources\News\NewsViewResource;
use App\Repositories\News\NewsRepository;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class NewsController extends Controller
{
    public function __construct(
        private readonly NewsRepository $news
    ) {
    }

    #[OA\Get(
        path: '/api/v1/news',
        summary: 'Получить список новостей',
        security: [],
        tags: ['Новости'],
        parameters: [
            new OA\Parameter(name: 'offset', ref: '#/components/parameters/Offset'),
            new OA\Parameter(name: 'limit', ref: '#/components/parameters/Limit'),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Новости',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/NewsResource'),
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
        $query = $this->news
            ->createApiQuery(authUser: \Auth::user())
            ->with(['media'])
        ;

        return NewsResource::collection($query->offsetPaginate((int)\request('limit')));
    }

    /**
     * @param string $slug
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    #[OA\Get(
        path: '/api/v1/news/{id}',
        summary: 'Получить новость',
        security: [],
        tags: ['Новости'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Объект новости',
                content: new OA\JsonContent(ref: '#/components/schemas/NewsViewResource'),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.not_found', response: Response::HTTP_NOT_FOUND),
        ],
    )]
    public function view(string $slug): JsonResource
    {
        $news = $this->news->createApiQuery(authUser: \Auth::user())->whereSlug($slug)->firstOrFail();

        $relatedNews = $this->news->findRelated(
            $news, 3, authUser: \Auth::user(),
        );

        return new NewsViewResource($news, $relatedNews);
    }

    /**
     * @param string $slug
     * @param \App\Services\ActivityService $activityService
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    #[OA\Post(
        path: '/api/v1/news/{slug}/like',
        summary: 'Поставить/убрать лайк',
        security: [
            ['bearer' => []],
        ],
        tags: ['Новости'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true),
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
    public function like(string $slug, ActivityService $activityService): JsonResponse
    {
        return \response()->json([
            'data' => [
                'is_liked' => $activityService->triggerLikeAsync(
                    \Auth::user(),
                    $this->news->createApiQuery(false)->whereSlug($slug)->firstOrFail(),
                ),
            ]
        ]);
    }
}
