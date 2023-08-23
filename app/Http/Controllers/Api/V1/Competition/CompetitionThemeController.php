<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Competition;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThemeResource;
use App\Models\Competition\CompetitionMasterClass;
use App\Repositories\Competition\CompetitionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class CompetitionThemeController extends Controller
{
    public function __construct(
        private readonly CompetitionRepository $competitions,
    ) {

    }

    #[OA\Get(
        path: '/api/v1/competitions/{slug}/themes/{theme}',
        summary: 'Страница темы внутри конкурса',
        security: [],
        tags: ['Конкурсы'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true),
            new OA\Parameter(name: 'theme', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1)),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Объект темы',
                content: new OA\JsonContent(ref: '#/components/schemas/ThemeResource'),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.not_found', response: Response::HTTP_NOT_FOUND),
        ],
    )]
    public function index(Request $request, string $slug, int $theme): JsonResponse
    {
        $competition = $this->competitions
            ->createApiQuery()
            ->where(['slug' => $slug])
            ->firstOrFail()
        ;

        /** @var \App\Models\Competition\Theme $theme */
        $theme = $competition->themes()->where(['id' => $theme])->firstOrFail();

        $masterClassIds = CompetitionMasterClass::query()
            ->where(['competition_id' => $competition->id])
            ->whereJsonContains('theme_ids', $theme->id)
            ->pluck('master_class_id')
            ->all()
        ;

        $masterClasses = $competition->masterClasses()
            ->with(['competitionsPreviews', 'lead'])
            ->withUserLikes($request->user())
            ->whereIn('id', $masterClassIds)
            ->get()
        ;

        return (new ThemeResource($theme, $masterClasses))->toResponse($request);
    }
}
