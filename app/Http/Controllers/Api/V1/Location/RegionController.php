<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Location;

use App\Http\Controllers\Controller;
use App\Http\Resources\Location\RegionResource;
use App\Repositories\Location\RegionRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class RegionController extends Controller
{
    public function __construct(
        private readonly RegionRepository $regions
    ) {
    }

    #[OA\Get(
        path: '/api/v1/location/regions',
        summary: 'Получить все регионы',
        security: [],
        tags: ['Местоположение'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Список регионов',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/RegionResource'),
                        ),
                    ]
                ),
            ),
        ],
    )]
    public function __invoke(): AnonymousResourceCollection
    {
        return RegionResource::collection(
            $this->regions->withoutPagination()
        );
    }
}
