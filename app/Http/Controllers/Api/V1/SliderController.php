<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SliderResource;
use App\Repositories\SliderRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class SliderController extends Controller
{
    public function __construct(
        private readonly SliderRepository $slides
    ) {
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    #[OA\Get(
        path: '/api/v1/sliders',
        summary: 'Получить список слайдов',
        security: [],
        tags: ['Слайдер'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Ведущие',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/SliderResource'),
                        ),
                    ]
                ),
            ),
        ],
    )]
    public function __invoke(): AnonymousResourceCollection
    {
        return SliderResource::collection($this->slides->all());
    }
}
