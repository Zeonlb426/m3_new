<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\User\SocialProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginSocialCallbackRequest;
use App\Http\Resources\Auth\TokenResource;
use App\Http\Resources\User\SelfUserResource;
use App\Services\User\AuthService;
use Laravel\Socialite\Facades\Socialite;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class LoginSocialController
 * @package App\Http\Controllers\Web
 */
final class LoginSocialController extends Controller
{
    /**
     * @param string $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    #[OA\Get(
        path: '/auth/login/{provider}',
        summary: 'Авторизация через соцсеть',
        security: [],
        tags: ['Авторизация'],
        parameters: [
            new OA\Parameter(
                name: 'provider',
                description: 'Провайдер для авторизации',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    ref: '#/components/schemas/SocialProviderField'
                )
            )
        ],
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
    public function login(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * @param \App\Http\Requests\Auth\LoginSocialCallbackRequest $request
     * @param string $provider
     * @param AuthService $authService
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Throwable
     */
    public function callback(
        LoginSocialCallbackRequest $request, string $provider, AuthService $authService
    ) {
        $provider = SocialProvider::tryFrom($provider);

        if (null === $provider) {
            throw new BadRequestHttpException();
        }

        [$user, $token] = $authService->loginOrRegisterSocial($provider);

        $data = [
            'data' => [
                'token' => TokenResource::make($token)->toArray($request),
                'user' => SelfUserResource::make($user)->toArray($request),
            ]
        ];

        return \view('auth', ['data' => $data]);
    }
}
