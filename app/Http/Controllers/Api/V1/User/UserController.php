<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\User;

use App\Exceptions\User\UserEmailAlreadyExistsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Requests\User\UserAvatarUpdateRequest;
use App\Http\Resources\Competition\CompetitionListResource;
use App\Http\Resources\CompetitionWork\WorkResource;
use App\Http\Resources\User\CreditsResource;
use App\Http\Resources\User\SelfUserResource;
use App\Models\User\UserTotalCredit;
use App\Repositories\Competition\CompetitionRepository;
use App\Repositories\CompetitionWork\WorkRepository;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class UserController extends Controller
{
    /**
     * @return \App\Http\Resources\User\SelfUserResource
     */
    #[OA\Get(
        path: '/api/v1/users/self',
        summary: 'Просмотр своего профиля',
        security: [
            ['bearer' => []],
        ],
        tags: ['Текущий пользователь'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Объект профиля',
                content: new OA\JsonContent(ref: '#/components/schemas/SelfUserResource'),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.unauthorized', response: Response::HTTP_UNAUTHORIZED),
        ],
    )]
    public function view(): JsonResource
    {
        return SelfUserResource::make(\Auth::user());
    }

    /**
     * @return \App\Http\Resources\User\SelfUserResource
     */
    #[OA\Get(
        path: '/api/v1/users/self/credits',
        summary: 'Получить счётчики кредитов',
        security: [
            ['bearer' => []],
        ],
        tags: ['Текущий пользователь'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Счётчики',
                content: new OA\JsonContent(ref: '#/components/schemas/CreditsResource'),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.unauthorized', response: Response::HTTP_UNAUTHORIZED),
        ],
    )]
    public function credits(): JsonResource
    {
        /* @var $user \App\Models\User */
        $user = \Auth::user();
        $user->loadMissing('totalCredit');
        return CreditsResource::make($user->totalCredit ?? new UserTotalCredit());
    }

    /**
     * @param \App\Repositories\CompetitionWork\WorkRepository $works
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    #[OA\Get(
        path: '/api/v1/users/self/works',
        summary: 'Получить конкурсные работы',
        security: [
            ['bearer' => []],
        ],
        tags: ['Текущий пользователь'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/Limit'),
            new OA\Parameter(ref: '#/components/parameters/Offset'),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Конкурсные работы',
                content: new OA\JsonContent(
                    type: 'object',
                    nullable: false,
                    properties: [
                        new OA\Property(property: 'data', type: 'array', nullable: false, items: new OA\Items(
                            ref: '#/components/schemas/WorkResource',
                        )),
                        new OA\Property(
                            property: 'statistics',
                            type: 'object',
                            nullable: false,
                            properties: [
                                new OA\Property(property: 'likes_count', type: 'integer', nullable: false, minimum: 0),
                            ],
                        ),
                        new OA\Property(property: 'links', ref: '#/components/schemas/LinksPaginatedResource'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/OffsetMetaPaginatedResource'),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.unauthorized', response: Response::HTTP_UNAUTHORIZED),
        ],
    )]
    public function works(WorkRepository $works): AnonymousResourceCollection
    {
        $query = $works
            ->createApiQuery(authUser: \Auth::user(), needResourceContent: true)
            ->with(['previewMedia'])
            ->whereUserId(\Auth::id())
        ;

        return WorkResource::collection($query->offsetPaginate((int)\request('limit', 15)))
            ->additional([
                'statistics' => [
                    'likes_count' => $works->countWorkLikesByUser(\Auth::user()),
                ],
            ])
        ;
    }

    /**
     * @param \App\Http\Requests\User\UpdateRequest $request
     * @param \App\Services\User\UserService $userService
     * @return \Illuminate\Http\Resources\Json\JsonResource
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     * @throws \Throwable
     */
    #[OA\Post(
        path: '/api/v1/users/self',
        summary: 'Обновление своего профиля',
        tags: ['Текущий пользователь'],
        security: [
            ['bearer' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(ref: '#/components/schemas/UserUpdateRequest')
                )
            ],
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Объект профиля',
                content: new OA\JsonContent(ref: '#/components/schemas/SelfUserResource'),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.unauthorized', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(
                ref: '#/components/responses/error.validation', response: Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
        ],
    )]
    public function update(UpdateRequest $request, UserService $userService): JsonResource
    {
        try {
            $user = $userService->update($request->user(), $request->getUpdateUser());
        } catch (UserEmailAlreadyExistsException) {
            throw ValidationException::withMessages([
                'email' => \__('validation.unique', ['attribute' => 'email']),
            ]);
        }

        return SelfUserResource::make($user);
    }

    /**
     * @param \App\Http\Requests\User\UserAvatarUpdateRequest $request
     * @param \App\Services\User\UserService $service
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    #[OA\Post(
        path: '/api/v1/users/self/avatar',
        summary: 'Обновление аватарки профиля',
        tags: ['Текущий пользователь'],
        security: [
            ['bearer' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(ref: '#/components/schemas/UserAvatarUpdateRequest')
                ),
            ],
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Объект профиля',
                content: new OA\JsonContent(ref: '#/components/schemas/SelfUserResource'),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.unauthorized', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(
                ref: '#/components/responses/error.validation', response: Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
        ],
    )]
    public function updateAvatar(UserAvatarUpdateRequest $request, UserService $service): JsonResponse
    {
        $user = $service->updateAvatar($request->user(), $request->validated('avatar'));

        return (new SelfUserResource($user))->toResponse($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Repositories\Competition\CompetitionRepository $competitions
     *
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/users/self/competitions',
        summary: 'Получить список конкурсов пользователя',
        security: [
            ['bearer' => []],
        ],
        tags: ['Текущий пользователь'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Конкурсные работы',
                content: new OA\JsonContent(
                    type: 'object',
                    nullable: false,
                    properties: [
                        new OA\Property(property: 'data', type: 'array', nullable: false, items: new OA\Items(
                            ref: '#/components/schemas/CompetitionListResource',
                        )),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.unauthorized', response: Response::HTTP_UNAUTHORIZED),
        ],
    )]
    public function competitions(
        Request $request, CompetitionRepository $competitions,
    ): JsonResponse {
        $competitionModels = $competitions->findUserCompetitions($request->user());

        return CompetitionListResource::collection($competitionModels)->toResponse($request);
    }
}
