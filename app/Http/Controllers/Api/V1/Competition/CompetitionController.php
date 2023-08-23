<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Competition;

use App\Http\Controllers\Controller;
use App\Http\Resources\Competition\CompetitionListResource;
use App\Http\Resources\Competition\CompetitionViewResource;
use App\Repositories\Competition\CompetitionRepository;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

final class CompetitionController extends Controller
{
    public function __construct(
        private readonly CompetitionRepository $competitions
    ) {
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    #[OA\Get(
        path: '/api/v1/competitions',
        summary: 'Получить список конкурсов',
        security: [],
        tags: ['Конкурсы'],
        parameters: [
            new OA\Parameter(name: 'offset', ref: '#/components/parameters/Offset'),
            new OA\Parameter(name: 'limit', ref: '#/components/parameters/Limit'),
            new OA\Parameter(
                name: 'filters[age_groups]',
                description: 'СЛАГИ возрастных групп (опционально). Множественные значения разделены запятой',
                in: 'query',
                required: false,
            ),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Конкурсы',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/CompetitionListResource'),
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
    public function index()
    {
        $query = $this->competitions->createApiQuery()->with('ageGroups');

        $query = QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::exact('age_groups', 'ageGroups.slug'),
            ])
            ->getEloquentBuilder()
        ;

        return CompetitionListResource::collection(
            $query->offsetPaginate((int)\request('limit', 15))
        );
    }

    /**
     * @param $slug
     * @return \App\Http\Resources\Competition\CompetitionViewResource
     */
    #[OA\Get(
        path: '/api/v1/competitions/{slug}',
        summary: 'Страница конкурса',
        security: [],
        tags: ['Конкурсы'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Объект конкурса',
                content: new OA\JsonContent(ref: '#/components/schemas/CompetitionViewResource'),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.not_found', response: Response::HTTP_NOT_FOUND),
        ],
    )]
    public function view($slug)
    {
        return CompetitionViewResource::make(
            $this
                ->competitions
                ->createApiQuery(true, \Auth::user())
                ->whereSlug($slug)
                ->firstOrFail()
        );
    }
}
