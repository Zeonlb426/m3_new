<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\MasterClass;

use App\Http\Controllers\Controller;
use App\Http\Resources\MasterClass\CourseResource;
use App\Repositories\MasterClass\CourseRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

final class CourseController extends Controller
{
    public function __construct(
        private readonly CourseRepository $courses
    ) {}

    /**
     * @param string|null $masterClassId
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    #[OA\Get(
        path: '/api/v1/master-classes/{masterClassId}/courses',
        summary: 'Получить курс мастер-класса',
        security: [],
        tags: ['Мастер-классы'],
        parameters: [
            new OA\Parameter(
                name: 'masterClassId',
                description: 'ID мастер-класса',
                in: 'path',
                required: true,
            ),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Курсы',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/CourseResource'),
                        ),
                    ]
                ),
            ),
        ],
    )]
    #[OA\Get(
        path: '/api/v1/master-classes/courses',
        summary: 'Получить список всех курсов',
        security: [],
        tags: ['Мастер-классы'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Курсы',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/CourseResource'),
                        ),
                    ]
                ),
            ),
        ],
    )]
    public function index(?string $masterClassId = null): AnonymousResourceCollection
    {
        return CourseResource::collection(
            isset($masterClassId)
                ? $this->courses->byMaserClassId($masterClassId)
                : $this->courses->createApiQuery()->get()
        );
    }

    /**
     * @param $id
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    #[OA\Get(
        path: '/api/v1/master-classes/courses/{id}',
        summary: 'Получить курс',
        security: [],
        tags: ['Мастер-классы'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Объект курса',
                content: new OA\JsonContent(ref: '#/components/schemas/CourseResource'),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.not_found', response: Response::HTTP_NOT_FOUND),
        ],
    )]
    public function view($id): JsonResource
    {
        return CourseResource::make($this->courses->createApiQuery()->findOrFail($id));
    }
}
