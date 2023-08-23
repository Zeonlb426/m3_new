<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Misc;

use App\Exceptions\FeedbackFrequencyException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Misc\CreateRequest;
use App\Repositories\Misc\FeedbackRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

final class FeedbackController extends Controller
{
    public function __construct(
        private readonly FeedbackRepository $feedbacks
    ) {}

    /**
     * @param \App\Http\Requests\Misc\CreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    #[OA\Post(
        path: '/api/v1/feedbacks',
        summary: 'Отправить форму обратной связи',
        security: [],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/FeedbackCreateRequest'),
        ),
        tags: ['Служебные'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Заявка сохранена',
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(
                ref: '#/components/responses/error.validation', response: Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
            new OA\Response(ref: '#/components/responses/error.too_many_requests', response: Response::HTTP_TOO_MANY_REQUESTS),
        ],
    )]
    public function __invoke(CreateRequest $request)
    {
        $validated = $request->validated();

        try {
            $this->feedbacks->create(
                $validated['name'],
                $validated['email'],
                $validated['content'],
                \Auth::user()
            );
        } catch (FeedbackFrequencyException $e) {
            throw new TooManyRequestsHttpException(
                $this->feedbacks::FREQUENCY_TIMEOUT - $e->getLastCreated()->diffInSeconds()
            );
        }

        return \response()->json([
            'data' => [
                'success' => true
            ]
        ]);
    }
}
