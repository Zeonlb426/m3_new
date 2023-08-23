<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\ErrorCodes;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

/**
 * Class Handler
 * @package App\Exceptions
 */
final class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [

    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'old_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->renderable(function (AuthorizationException $exception, $request) {
            return $this->forbidden($request, $exception);
        });

        $this->renderable(function (AccessDeniedHttpException $exception, $request) {
            return $this->forbidden($request, $exception);
        });

        $this->reportable(static function (Throwable $e) {

        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e): Response
    {
        if (\method_exists($e, 'render') && $response = $e->render($request)) {
            return Router::toResponse($request, $response);
        }

        if ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        $e = $this->prepareException($this->mapException($e));

        foreach ($this->renderCallbacks as $renderCallback) {
            if (\is_a($e, $this->firstClosureParameterType($renderCallback))) {
                $response = $renderCallback($e, $request);

                if (!\is_null($response)) {
                    return $response;
                }
            }
        }

        $expectsJson = $this->shouldReturnJson($request, $e);

        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        } else if ($e instanceof UnauthorizedHttpException) {
            return $this->basicAuthFailed($request, $e);
        } elseif ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        } elseif ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e, $request);
        } elseif ($e instanceof BadRequestHttpException && $expectsJson) {
            return $this->badRequest($request, $e);
        } elseif ($e instanceof NotFoundHttpException && $expectsJson) {
            return $this->notFound($request, $e);
        } elseif ($e instanceof GoneHttpException && $expectsJson) {
            return $this->gone($request, $e);
        } elseif ($e instanceof TooManyRequestsHttpException) {
            return $this->tooManyRequests(
                $request,
                $e,
                $e->getHeaders()['Retry-After'] ?? null
            );
        }

        return $expectsJson
            ? $this->prepareJsonResponse($request, $e)
            : $this->prepareResponse($request, $e);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Symfony\Component\HttpKernel\Exception\BadRequestHttpException $exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function badRequest(
        Request $request, BadRequestHttpException $exception
    ): Response {
        return \response()->json([
            'code' => ErrorCodes::BAD_REQUEST,
            'message' => $exception->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param NotFoundHttpException $exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function notFound(
        Request $request, NotFoundHttpException $exception
    ): Response {
        return \response()->json([
            'code' => ErrorCodes::NOT_FOUND,
            'message' => $exception->getMessage(),
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * @param $request
     * @param $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function gone($request, $exception): Response
    {
        return \response()->json([
            'code' => ErrorCodes::GONE,
            'message' => $exception->getMessage(),
        ], Response::HTTP_GONE);
    }

    /**
     * @param $request
     * @param $exception
     * @param $retryAfter
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tooManyRequests($request, $exception, $retryAfter): Response
    {
        return \response()->json([
            'code' => ErrorCodes::TOO_MANY_REQUESTS,
            'message' => $exception->getMessage(),
            'retry_after' => $retryAfter,
        ], Response::HTTP_TOO_MANY_REQUESTS);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException $exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function basicAuthFailed(Request $request, UnauthorizedHttpException $exception): Response
    {
        return \response('', $exception->getStatusCode(), $exception->getHeaders());
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception): Response
    {
        // if request expects json content
        // or request path start with api
        return $this->shouldReturnJson($request, $exception)
            ? \response()->json([
                'code' => ErrorCodes::UNAUTHORIZED,
                'message' => $exception->getMessage(),
            ], Response::HTTP_FORBIDDEN)
            : \redirect()->guest($exception->redirectTo() ?? \route('login'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Auth\Access\AuthorizationException|\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function forbidden(Request $request, $exception): Response
    {
        // if request expects json content
        // or request path start with api
        return $this->shouldReturnJson($request, $exception)
            ? \response()->json([
                'code' => ErrorCodes::FORBIDDEN,
                'message' => $exception->getMessage(),
            ], Response::HTTP_FORBIDDEN)
            : $this->prepareResponse($request, $exception);
    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param \Illuminate\Validation\ValidationException $e
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $e->response;
        }

        if (false === $this->shouldReturnJson($request, $e)) {
            return $this->invalid($request, $e);
        }

        return \response()->json([
            'code' => ErrorCodes::FAILED_VALIDATION,
            'message' => \__('messages.exception.given_data_invalid'),
            'errors' => $this->formatErrorMessages($e->errors()),
        ], $e->status);
    }

    /**
     * @param array $errors
     *
     * @return array
     */
    protected function formatErrorMessages(array $errors): array
    {
        foreach ($errors as $index => $error) {
            if (\is_array($error)) {
                $errors[$index] = \array_shift($error);
            }
        }

        return $errors;
    }

    /**
     * {@inheritDoc}
     */
    protected function shouldReturnJson($request, Throwable $e)
    {
        return $request->isApiRequest();
    }

    /**
     * Convert a validation exception into a response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Validation\ValidationException $exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function invalid($request, ValidationException $exception): RedirectResponse
    {
        return \redirect($exception->redirectTo ?? 'home')
            ->withInput(Arr::except($request->input(), $this->dontFlash))
            ->withErrors($exception->errors(), $request->input('_error_bag', $exception->errorBag))
        ;
    }
}
