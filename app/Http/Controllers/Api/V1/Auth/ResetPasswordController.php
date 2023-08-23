<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\User\PasswordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResetPasswordController
 * @package App\Http\Controllers\Api\v1\Auth
 */
final class ResetPasswordController extends Controller
{
    public function __construct(
        private readonly PasswordService $passwordService
    ) {}

    /**
     * @param \App\Http\Requests\Auth\ForgotPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    #[OA\Post(
        path: '/api/v1/auth/forgot-password',
        summary: 'Запрос на сброс пароля',
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ForgotPasswordRequest'),
        ),
        tags: ['Сброс пароля'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Письмо успешно отправлено',
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(
                ref: '#/components/responses/error.validation', response: Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
        ],
    )]
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->passwordService->sendForgot($request->validated('email'));
        if (true !== $status) {
            throw ValidationException::withMessages([
                'email' => \sprintf('%s: %s', \__('messages.exception.error_email_sending'), $status)
            ]);
        }

        return \response()->json();
    }

    /**
     * @param \App\Http\Requests\Auth\ResetPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    #[OA\Post(
        path: '/api/v1/auth/reset-password',
        summary: 'Ввод нового пароля',
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ResetPasswordRequest'),
        ),
        tags: ['Сброс пароля'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Пароль успешно сброшен',
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(
                ref: '#/components/responses/error.validation', response: Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
        ],
    )]
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->passwordService->doReset(
            $request->validated('email'),
            $request->validated('token'),
            $request->validated('password'),
        );
        if (true !== $status) {
            throw ValidationException::withMessages([
                'email' => \sprintf('%s: %s', \__('messages.exception.error_reset_password'), $status)
            ]);
        }

        return \response()->json();
    }
}
