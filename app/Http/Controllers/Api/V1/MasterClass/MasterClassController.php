<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterClass;

use App\Enums\MasterClass\AdditionalSign;
use App\Http\Controllers\Controller;
use App\Http\Resources\MasterClass\MasterClassResource;
use App\Http\Resources\MasterClass\MasterClassViewResource;
use App\Repositories\MasterClass\MasterClassRepository;
use App\Services\ActivityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

final class MasterClassController extends Controller
{
    public function __construct(
        private readonly MasterClassRepository $masterClasses
    ) {
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    #[OA\Get(
        path: '/api/v1/master-classes',
        summary: 'Получить список мастер классов',
        security: [],
        tags: ['Мастер-классы'],
        parameters: [
            new OA\Parameter(
                name: 'filters[age_groups]',
                description: 'СЛАГИ возрастных групп (опционально). Множественные значения разделены запятой',
                in: 'query',
                required: false,
            ),
            new OA\Parameter(
                name: 'filters[courses]',
                description: 'СЛАГИ курсов (опционально). Множественные значения разделены запятой',
                in: 'query',
                required: false,
            ),
            new OA\Parameter(
                name: 'filters[marks]',
                description: 'Метка мастер-класса (опционально). Множественные значения разделены запятой',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    ref: '#/components/schemas/AdditionalMarkField'
                )
            ),
            new OA\Parameter(name: 'offset', ref: '#/components/parameters/Offset'),
            new OA\Parameter(name: 'limit', ref: '#/components/parameters/Limit'),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Мастер классы',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/MasterClassResource'),
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
        $query = $this->masterClasses->createApiQuery(authUser: \Auth::user());

        $query = QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::exact('age_groups', 'ageGroup.slug'),
                AllowedFilter::exact('courses', 'courses.slug'),
                AllowedFilter::callback('marks', function (Builder $query, $value): Builder {
                    /** @var \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass $query */

                    $signs = \array_unique(\is_array($value) ? $value : [$value]);

                    foreach ($signs as $sign) {
                        if (null === ($sign = AdditionalSign::tryFrom($sign))) {
                            continue;
                        }

                        $query = $query->signHas($sign);

                        if (AdditionalSign::GENERAL === $sign) {
                            $query = $query->with(['competitionsPreviews']);
                        }
                    }

                    return $query;
                }, arrayValueDelimiter: ','),
            ])
            ->getEloquentBuilder()
        ;

        return MasterClassResource::collection(
            $query->offsetPaginate((int)\request('limit', 15))
        );
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    #[OA\Get(
        path: '/api/v1/master-classes/{id}',
        summary: 'Получить мастер класс',
        security: [],
        tags: ['Мастер-классы'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Объект мастер класса',
                content: new OA\JsonContent(ref: '#/components/schemas/MasterClassViewResource'),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.not_found', response: Response::HTTP_NOT_FOUND),
        ],
    )]
    public function view(int $id): JsonResource
    {
        return MasterClassViewResource::make(
            $this->masterClasses->createApiQuery(authUser: \Auth::user())->findOrFail($id)
        );
    }

    /**
     * @param int $id
     * @param \App\Services\ActivityService $activityService
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    #[OA\Post(
        path: '/api/v1/master-classes/{id}/like',
        summary: 'Поставить/убрать лайк',
        security: [
            ['bearer' => []],
        ],
        tags: ['Мастер-классы'],
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
    public function like(int $id, ActivityService $activityService): JsonResponse
    {
        return \response()->json([
            'data' => [
                'is_liked' => $activityService->triggerLikeAsync(
                    \Auth::user(),
                    $this->masterClasses->createApiQuery(false)->findOrFail($id)
                ),
            ]
        ]);
    }
}
