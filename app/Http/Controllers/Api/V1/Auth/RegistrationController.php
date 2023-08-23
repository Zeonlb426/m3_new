<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Exceptions\User\UserEmailAlreadyExistsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Http\Resources\Auth\TokenResource;
use App\Http\Resources\User\SelfUserResource;
use App\Services\User\AuthService;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RegistrationController
 * @package App\Http\Controllers\Api\v1\Auth
 */
final class RegistrationController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly AuthService $authService,
    ) {
    }

    /**
     * @param \App\Http\Requests\Auth\RegistrationRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    #[OA\Post(
        path: '/api/v1/auth/registration',
        summary: 'Регистрация',
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegistrationRequest'),
        ),
        tags: ['Регистрация'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Регистрация успешна',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'token', ref: '#/components/schemas/TokenResource', type: 'object'),
                                new OA\Property(property: 'user', ref: '#/components/schemas/SelfUserResource', type: 'object'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(
                ref: '#/components/responses/error.validation', response: Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
        ],
    )]
    public function __invoke(RegistrationRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->register($request->getRegisterUser());
        } catch (UserEmailAlreadyExistsException) {
            throw ValidationException::withMessages([
                'email' => \__('validation.unique', ['attribute' => 'email'])
            ]);
        }

        $token = $this->authService->login($user);

        return \response()->json([
            'data' => [
                'token' => TokenResource::make($token)->toArray($request),
                'user' => SelfUserResource::make($user)->toArray($request),
            ],
        ]);
    }
}
