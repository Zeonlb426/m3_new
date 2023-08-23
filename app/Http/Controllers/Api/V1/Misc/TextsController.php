<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\Misc;

use App\Http\Controllers\Controller;
use App\Settings\MainTextsSettings;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class TextsController extends Controller
{
    /**
     * @param \App\Settings\MainTextsSettings $settings
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/texts',
        summary: 'Получить тексты',
        security: [],
        tags: ['Служебные'],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Тексты',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'meta',
                                    properties: [
                                        new OA\Property(property: 'title', type: 'string', nullable: true),
                                        new OA\Property(property: 'keywords', type: 'string', nullable: true),
                                        new OA\Property(property: 'description', type: 'string', nullable: true),
                                    ],
                                    type: 'object',
                                ),
                                new OA\Property(
                                    property: 'sharing',
                                    properties: [
                                        new OA\Property(property: 'title', type: 'string', nullable: true),
                                        new OA\Property(property: 'description', type: 'string', nullable: true),
                                        new OA\Property(property: 'image', type: 'string', nullable: true),
                                    ],
                                    type: 'object',
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function __invoke(MainTextsSettings $settings): JsonResponse
    {
        return \response()->json([
            'data' => [
                'meta' => [
                    'title' => $settings->meta_title ?: null,
                    'keywords' => $settings->meta_keywords ?: null,
                    'description' => $settings->meta_description ?: null,
                ],
                'sharing' => [
                    'title' => $settings->sharing_title ?: null,
                    'description' => $settings->sharing_description ?: null,
                    'image' => $settings->sharing_image ? \url($settings->sharing_image) : null,
                ],
            ]
        ]);
    }
}
