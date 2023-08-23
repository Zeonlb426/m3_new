<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\Location;

use App\Http\Controllers\Controller;
use App\Http\Resources\Location\CityResource;
use App\Repositories\Location\CityRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class CityController extends Controller
{
    public function __construct(
        private readonly CityRepository $cities
    ) {}

    #[OA\Get(
        path: '/api/v1/location/cities/{regionId}',
        summary: 'Получить города по региону',
        security: [],
        tags: ['Местоположение'],
        parameters: [
            new OA\Parameter(name: 'regionId', in: 'path', required: false),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Список городов',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/CityResource'),
                        ),
                    ]
                ),
            ),
        ],
    )]
    public function __invoke(?int $regionId = null): AnonymousResourceCollection
    {
        return CityResource::collection(
            isset($regionId)
                ? $this->cities->byRegionWithoutPagination($regionId)
                : $this->cities->withoutPagination()
        );
    }
}
