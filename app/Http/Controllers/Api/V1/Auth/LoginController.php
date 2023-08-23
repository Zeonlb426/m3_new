<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\TokenResource;
use App\Http\Resources\User\SelfUserResource;
use App\Services\User\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class LoginController extends Controller
{
    /**
     * @param \App\Http\Requests\Auth\LoginRequest $request
     * @param \App\Services\User\AuthService $authService
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Post(
        path: '/api/v1/auth/login',
        summary: 'Авторизация по паролю',
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest'),
        ),
        tags: ['Авторизация'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Авторизация успешна',
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
    public function login(LoginRequest $request, AuthService $authService): JsonResponse
    {
        $result = $authService->loginByCredentials(
            $request->validated('email'),
            $request->validated('password'),
            (bool)(int)$request->get('remember', 0)
        );

        if (null === $result) {
            throw ValidationException::withMessages(['email' => \__('auth.failed')]);
        }

        [$user, $token] = $result;

        $user->load(['region', 'city']);

        return \response()->json([
            'data' => [
                'token' => TokenResource::make($token)->toArray($request),
                'user' => SelfUserResource::make($user)->toArray($request),
            ],
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Delete(
        path: '/api/v1/auth/logout',
        summary: 'Сбросить текущий токен авторизации',
        security: [
            ['bearer' => []],
        ],
        tags: ['Авторизация'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Токен сброшен',
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.unauthorized', response: Response::HTTP_UNAUTHORIZED),
            new OA\Response(
                ref: '#/components/responses/error.validation', response: Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
        ],
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return \response()->json();
    }
}
