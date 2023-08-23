<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Competition;

use App\Http\Controllers\Controller;
use App\Http\Resources\Competition\PartnerResource;
use App\Repositories\Competition\PartnerRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class PartnerController extends Controller
{
    /**
     * @param \App\Repositories\Competition\PartnerRepository $partners
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    #[OA\Get(
        path: '/api/v1/competitions/partners',
        summary: 'Получить список партнёров',
        security: [],
        tags: ['Конкурсы'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Партнёры',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/PartnerResource'),
                        ),
                    ]
                ),
            ),
        ],
    )]
    public function __invoke(PartnerRepository $partners): AnonymousResourceCollection
    {
        return PartnerResource::collection(
            $partners->createApiQuery()->get()
        );
    }
}
