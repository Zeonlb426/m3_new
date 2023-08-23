<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Misc;

use App\Http\Controllers\Controller;
use App\Models\Misc\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class DocumentController extends Controller
{
    /**
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/documents/{slug}',
        summary: 'Получить документ',
        security: [],
        tags: ['Служебные'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Ссылка на документ',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'link', type: 'string'),
                                new OA\Property(property: 'name', type: 'string'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.not_found', response: Response::HTTP_NOT_FOUND),
        ],
    )]
    public function __invoke(string $slug): JsonResponse
    {
        $document = Document::query()->whereSlug($slug)->firstOrFail();
        return \response()->json([
            'data' => [
                'link' => $document->file,
                'name' => $document->file_name,
            ],
        ]);
    }


    #[OA\Get(
        path: '/api/v1/file/{slug}',
        summary: 'Получить документ',
        security: [],
        tags: ['Служебные'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Файл',
                content: new OA\MediaType(
                    mediaType: 'application/pdf',
                ),
            ),
            new OA\Response(ref: '#/components/responses/error.bad_request', response: Response::HTTP_BAD_REQUEST),
            new OA\Response(ref: '#/components/responses/error.not_found', response: Response::HTTP_NOT_FOUND),
        ],
    )]
    public function getFile(string $slug)
    {
        $document = Document::query()->whereSlug($slug)->firstOrFail();
        $headers = ['Content-Type: application/pdf'];

        $file = $document->getFirstMedia(Document::FILE_COLLECTION);

        Storage::disk('local')->put($document->file_name . '.pdf', Storage::get($file->getPath()));

        return \response()->file(storage_path('app/' . $document->file_name . '.pdf'),$headers);

//        return response()->redirectTo($document->file, 302, $headers );
    }
}
