<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\CompetitionWork;

use App\Enums\User\ActionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionWork\CreateRequest;
use App\Http\Resources\CompetitionWork\WorkResource;
use App\Repositories\CompetitionWork\WorkRepository;
use App\Services\ActivityService;
use App\Services\Competition\WorkService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

final class WorkController extends Controller
{
    public function __construct(
        private readonly WorkRepository $works,
        private readonly ActivityService $activityService
    ) {
    }

    /**
     * @param string $slug
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    #[OA\Get(
        path: '/api/v1/competitions/{slug}/works',
        summary: 'Получить список конкурсных работ',
        security: [],
        tags: ['Конкурсы'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                description: 'Для получения всех работ пользователей, вне зависимости от конкурса: slug = all',
                in: 'path',
                required: true
            ),
            new OA\Parameter(name: 'offset', ref: '#/components/parameters/Offset'),
            new OA\Parameter(name: 'limit', ref: '#/components/parameters/Limit'),
            new OA\Parameter(
                name: 'filters[author]',
                description: 'Поиск по ФИО автора',
                in: 'query',
                required: false,
            ),
            new OA\Parameter(
                name: 'filters[themes]',
                description: 'ID тем (опционально). Множественные значения разделены запятой',
                in: 'query',
                required: false,
            ),
            new OA\Parameter(
                name: 'filters[region]',
                description: 'ID региона (опционально).',
                in: 'query',
                required: false,
            ),
            new OA\Parameter(
                name: 'filters[age_group]',
                description: 'СЛАГ возрастной группы (опционально)',
                in: 'query',
                required: false,
            ),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Работы',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/WorkResource'),
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
    public function showByCompetition(string $slug): AnonymousResourceCollection
    {
        $query = $this->works
            ->createApiQuery(true, \Auth::user())
            ->with(['previewMedia'])
            ->visible()
        ;

        if (\strtolower($slug) !== 'all') {
            $query = $query->whereHas('competition', function (Builder $query) use ($slug): Builder {
                /** @var \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition $query */
                return $query->whereSlug($slug);
            });
        }

        $query = QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::callback('author', function (Builder $query, $value) {
                    /** @var \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work $query */
                    return $query->whereHas('author', function (Builder $query) use ($value): Builder {
                        /** @var \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\WorkAuthor $query */
                        return $query->nameLike($value);
                    });
                }, arrayValueDelimiter: ','),
                AllowedFilter::callback('themes', function (Builder $query, $value): Builder {
                    /** @var \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work $query */
                    return $query->whereHas('theme', function (Builder $query) use ($value): Builder {
                        /** @var \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Theme $query */
                        return $query->whereKey(\array_map(fn($v) => (int)$v, Arr::wrap($value)));
                    });
                }, arrayValueDelimiter: ','),
                AllowedFilter::callback('region', function (Builder $query, $value): Builder {
                    /** @var \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work $query */
                    return $query->whereHas('user', function (Builder $query) use ($value): Builder {
                        /** @var \Illuminate\Database\Eloquent\Builder|\App\Models\User $query */
                        $value = \array_map(fn($v) => (int)$v, Arr::wrap($value));

                        return 1 === \count($value)
                            ? $query->whereRegionId($value[0])
                            : $query->whereIn('region_id', $value);
                    });
                }, arrayValueDelimiter: ','),
                AllowedFilter::exact('age_group', 'competition.ageGroups.slug'),
            ])
            ->getEloquentBuilder()
        ;

        return WorkResource::collection(
            $query->offsetPaginate((int)\request('limit', 15))
        );
    }

    /**
     * @param \App\Http\Requests\CompetitionWork\CreateRequest $request
     * @param string $slug
     * @param \App\Services\Competition\WorkService $service
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     * @throws \Throwable
     */
    #[OA\Post(
        path: '/api/v1/competitions/{slug}/works',
        summary: 'Создать конкурсную работу',
        tags: ['Конкурсы'],
        security: [
            ['bearer' => []],
        ],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(ref: '#/components/schemas/WorkCreateRequest')
                )
            ],
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Работа сохранена',
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.unauthorized', response: Response::HTTP_UNAUTHORIZED),

            new OA\Response(
                ref: '#/components/responses/error.validation', response: Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
            new OA\Response(ref: '#/components/responses/error.too_many_requests', response: Response::HTTP_TOO_MANY_REQUESTS),
        ],
    )]
    public function create(CreateRequest $request, string $slug, WorkService $service): JsonResponse
    {
        $work = $service->create($request->user(), $request->getCreateWork());

        $this->activityService->addAction($request->user(), $work, ActionType::ADD_WORK);

        return \response()->json([
            'data' => [
                'success' => true
            ],
        ]);
    }

    /**
     * @param int $workId
     * @param \App\Services\ActivityService $activityService
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    #[OA\Post(
        path: '/api/v1/competitions/works/{workId}/like',
        summary: 'Поставить/убрать лайк',
        security: [
            ['bearer' => []],
        ],
        tags: ['Конкурсы'],
        parameters: [
            new OA\Parameter(name: 'workId', in: 'path', required: true),
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
            new OA\Response(
                response: Response::HTTP_PRECONDITION_FAILED,
                description: 'Нелязь поставить лайк на собственную работу',
            ),
        ],
    )]
    public function like(int $workId, ActivityService $activityService): JsonResponse
    {
        $user = \Auth::user();

        $work = $this->works->createApiQuery(authUser: $user)
            ->visible()
            ->whereKey($workId)
            ->firstOrFail()
        ;

        if ($work->user_id === $user->id) {
            return \response()->json([
                'message' => 'Нельзя поставить лайк на собственную работу',
            ])->setStatusCode(Response::HTTP_PRECONDITION_FAILED);
        }

        return \response()->json([
            'data' => [
                'is_liked' => $activityService->triggerLikeAsync($user, $work),
            ],
        ]);
    }
}
