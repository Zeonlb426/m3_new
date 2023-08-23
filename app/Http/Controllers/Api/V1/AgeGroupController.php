<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AgeGroupResource;
use App\Models\AgeGroup;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class AgeGroupController extends Controller
{
    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    #[OA\Get(
        path: '/api/v1/age-groups',
        summary: 'Получить список возрастных групп',
        security: [],
        tags: ['Конкурсы и мастер-классы'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Возрастные группы',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/AgeGroupResource'),
                        ),
                    ]
                ),
            ),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        return AgeGroupResource::collection(
            AgeGroup::query()
                ->orderBy('min_age')
                ->orderBy('max_age')
                ->get()
        );
    }

    /**
     * @param $id
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    #[OA\Get(
        path: '/api/v1/age-groups/{id}',
        summary: 'Получить возрастную группу',
        security: [],
        tags: ['Конкурсы и мастер-классы'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Объект возрастной группы',
                content: new OA\JsonContent(ref: '#/components/schemas/AgeGroupResource'),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.not_found', response: Response::HTTP_NOT_FOUND),
        ],
    )]
    public function view(int $id): JsonResource
    {
        return AgeGroupResource::make(AgeGroup::query()->findOrFail($id));
    }
}
