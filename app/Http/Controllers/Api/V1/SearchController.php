<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\SearchCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\Competition\CompetitionSearchResource;
use App\Http\Resources\CompetitionWork\WorkResource;
use App\Http\Resources\MasterClass\MasterClassResource;
use App\Http\Resources\News\NewsSearchResource;
use App\Repositories\Competition\CompetitionRepository;
use App\Repositories\CompetitionWork\WorkRepository;
use App\Repositories\MasterClass\MasterClassRepository;
use App\Repositories\News\NewsRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class SearchController extends Controller
{
    public function __construct(
        private readonly MasterClassRepository $masterClasses,
        private readonly NewsRepository $news,
        private readonly CompetitionRepository $competitions,
        private readonly WorkRepository $works,
    ) {
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/search',
        summary: 'Поиск по работам пользователей, конкурсам, мастер-классам и новостям',
        security: [],
        tags: ['Поиск по сайту'],
        parameters: [
            new OA\Parameter(
                name: 'text',
                description: 'Текст поиска.',
                in: 'query',
                required: true,
            ),
            new OA\Parameter(
                name: 'category',
                description: 'Раздел поиска (опционально). Если не указан - поиск осуществляется по всем категориям.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    ref: '#/components/schemas/SearchCategoryField'
                )
            ),
            new OA\Parameter(name: 'offset', ref: '#/components/parameters/Offset'),
            new OA\Parameter(name: 'limit', ref: '#/components/parameters/Limit'),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Результат поиска',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'news',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/NewsSearchResource'),
                                ),
                                new OA\Property(property: 'offset', type: 'integer'),
                                new OA\Property(property: 'prev', type: 'integer', nullable: true),
                                new OA\Property(property: 'next', type: 'integer', nullable: true),
                                new OA\Property(property: 'limit', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                                new OA\Property(property: 'next_page_url', type: 'string', nullable: true),
                                new OA\Property(property: 'prev_page_url', type: 'string', nullable: true),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'works',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/WorkResource'),
                                ),
                                new OA\Property(property: 'offset', type: 'integer'),
                                new OA\Property(property: 'prev', type: 'integer', nullable: true),
                                new OA\Property(property: 'next', type: 'integer', nullable: true),
                                new OA\Property(property: 'limit', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                                new OA\Property(property: 'next_page_url', type: 'string', nullable: true),
                                new OA\Property(property: 'prev_page_url', type: 'string', nullable: true),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'competitions',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/CompetitionSearchResource'),
                                ),
                                new OA\Property(property: 'offset', type: 'integer'),
                                new OA\Property(property: 'prev', type: 'integer', nullable: true),
                                new OA\Property(property: 'next', type: 'integer', nullable: true),
                                new OA\Property(property: 'limit', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                                new OA\Property(property: 'next_page_url', type: 'string', nullable: true),
                                new OA\Property(property: 'prev_page_url', type: 'string', nullable: true),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'master_classes',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/MasterClassResource'),
                                ),
                                new OA\Property(property: 'offset', type: 'integer'),
                                new OA\Property(property: 'prev', type: 'integer', nullable: true),
                                new OA\Property(property: 'next', type: 'integer', nullable: true),
                                new OA\Property(property: 'limit', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                                new OA\Property(property: 'next_page_url', type: 'string', nullable: true),
                                new OA\Property(property: 'prev_page_url', type: 'string', nullable: true),
                            ],
                            type: 'object'
                        ),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
        ],
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'text' => 'required|max:255',
            'category' => ['nullable', Rule::enum(SearchCategory::class)],
        ]);

        $searchText = $validatedData['text'];
        $category = $validatedData['category'] ?? null;

        switch ($category) {
            case SearchCategory::NEWS->value:
                $collectionNews = NewsSearchResource::collection(
                    $this->news->searchNews($searchText)
                        ->offsetPaginate((int)\request('limit', 6))
                )->resource;
                break;
            case SearchCategory::WORKS->value:
                $collectionWorks = WorkResource::collection(
                    $this->works->searchWorks($searchText)
                        ->offsetPaginate((int)\request('limit', 6))
                )->resource;
                break;
            case SearchCategory::COMPETITIONS->value:
                $collectionCompetitions = CompetitionSearchResource::collection(
                    $this->competitions->searchCompetitions($searchText)
                        ->offsetPaginate((int)\request('limit', 6))
                )->resource;
                break;
            case SearchCategory::MASTER_CLASSES->value:
                $collectionMasterClass = MasterClassResource::collection(
                    $this->masterClasses->searchMasterClass($searchText)
                        ->offsetPaginate((int)\request('limit', 6))
                )->resource;
                break;
            default:
                $collectionMasterClass = MasterClassResource::collection(
                    $this->masterClasses->searchMasterClass($searchText)
                        ->offsetPaginate((int)\request('limit', 6))
                )->resource;
                $collectionCompetitions = CompetitionSearchResource::collection(
                    $this->competitions->searchCompetitions($searchText)
                        ->offsetPaginate((int)\request('limit', 6))
                )->resource;
                $collectionWorks = WorkResource::collection(
                    $this->works->searchWorks($searchText)
                        ->offsetPaginate((int)\request('limit', 6))
                )->resource;
                $collectionNews = NewsSearchResource::collection(
                    $this->news->searchNews($searchText)
                        ->offsetPaginate((int)\request('limit', 6))
                )->resource;
        }

        return \response()->json([
                'news' => $collectionNews ?? null,
                'works' => $collectionWorks ?? null,
                'competitions' => $collectionCompetitions ?? null,
                'master_classes' => $collectionMasterClass ?? null,
        ]);
    }
}
