<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeadResource;
use App\Repositories\LeadRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

final class LeadController extends Controller
{
    public function __construct(
        private readonly LeadRepository $leads
    ) { }
    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */

    #[OA\Get(
        path: '/api/v1/leads',
        summary: 'Получить список ведущих',
        security: [],
        tags: ['Конкурсы и мастер-классы'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Ведущие',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/LeadResource'),
                        ),
                    ]
                ),
            ),
        ],
    )]
    public function index(): AnonymousResourceCollection
    {
        return LeadResource::collection(
            $this->leads->createApiQuery()->get()
        );
    }

    /**
     * @param $id
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    #[OA\Get(
        path: '/api/v1/leads/{id}',
        summary: 'Получить ведущего',
        security: [],
        tags: ['Конкурсы и мастер-классы'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Объект ведущего',
                content: new OA\JsonContent(ref: '#/components/schemas/LeadResource'),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.not_found', response: Response::HTTP_NOT_FOUND),
        ],
    )]
    public function view($id): JsonResource
    {
        return LeadResource::make(
            $this->leads->createApiQuery()->findOrFail($id)
        );
    }
}
