<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ErrorCodes;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

/**
 * Class Controller
 * @package App\Http\Controllers
 */
#[OA\OpenApi(
    info: new OA\Info(version: "1.0.0", title: L5_SWAGGER_APP_NAME),
    servers: [
        new OA\Server(url: L5_SWAGGER_CONST_HOST, description: 'Api'),
    ],
    tags: [],
    components: new OA\Components(
        schemas: [
            new OA\Schema(
                schema: 'error.unauthorized',
                description: 'Unauthorized',
                properties: [
                    new OA\Property(
                        property: 'message',
                        description: 'Текст ошибки',
                        type: 'string',
                        example: 'messages.exception.unauthorized',
                    ),
                    new OA\Property(
                        property: 'code', type: 'integer', default: ErrorCodes::UNAUTHORIZED, nullable: false,
                    ),
                ],
            ),
            new OA\Schema(
                schema: 'error.forbidden',
                description: 'Forbidden',
                properties: [
                    new OA\Property(
                        property: 'message',
                        description: 'Текст ошибки',
                        type: 'string',
                        example: 'messages.exception.forbidden',
                    ),
                    new OA\Property(property: 'code', type: 'integer', default: ErrorCodes::FORBIDDEN, nullable: false),
                ],
                type: 'object',
            ),
            new OA\Schema(
                schema: 'error.too_many_requests',
                description: 'Too many requests',
                properties: [
                    new OA\Property(
                        property: 'message',
                        description: 'Текст ошибки',
                        type: 'string',
                        example: 'messages.exception.too_many_requests',
                    ),
                    new OA\Property(property: 'code', type: 'integer', default: ErrorCodes::TOO_MANY_REQUESTS, nullable: false),
                    new OA\Property(
                        property: 'retry_after',
                        description: 'NULLABLE Попробуйте через (секунды)',
                        type: 'integer',
                        example: 'null|1',
                    ),
                ],
                type: 'object',
            ),
            new OA\Schema(
                schema: 'error.server',
                properties: [
                    new OA\Property(
                        property: 'message',
                        description: 'error message',
                        type: 'string',
                        example: 'messages.exception.server',
                    ),
                    new OA\Property(property: 'code', type: 'integer', default: ErrorCodes::INTERNAL_SERVER_ERROR, nullable: false),
                ],
                type: 'object',
            ),
            new OA\Schema(
                schema: 'MetaLinksPaginatedResource',
                properties: [
                    new OA\Property(property: 'url', type: 'string', nullable: false),
                    new OA\Property(property: 'label', type: 'string', nullable: false),
                    new OA\Property(property: 'active', type: 'bool', nullable: false),
                ],
                type: 'object',
            ),
            new OA\Schema(
                schema: 'LinksPaginatedResource',
                properties: [
                    new OA\Property(property: 'first', type: 'string', nullable: false),
                    new OA\Property(property: 'last', type: 'string', nullable: false),
                    new OA\Property(property: 'next', type: 'string', nullable: true),
                    new OA\Property(property: 'prev', type: 'string', nullable: true),
                ],
                type: 'object',
            ),
            new OA\Schema(
                schema: 'OffsetMetaPaginatedResource',
                type: 'object',
                properties: [
                    new OA\Property(property: 'offset', type: 'integer', nullable: false, minimum: 0),
                    new OA\Property(property: 'limit', type: 'integer', nullable: false, minimum: 1),
                    new OA\Property(property: 'total', type: 'integer', nullable: false, minimum: 0),
                    new OA\Property(property: 'prev', type: 'string', nullable: true),
                    new OA\Property(property: 'next', type: 'string', nullable: true),
                ],
            ),
            new OA\Schema(
                schema: 'MetaPaginatedResource',
                properties: [
                    new OA\Property(property: 'current_page', type: 'number', nullable: false),
                    new OA\Property(property: 'last_page', type: 'number', nullable: false),
                    new OA\Property(property: 'per_page', type: 'number', nullable: false),
                    new OA\Property(property: 'from', type: 'number', nullable: false),
                    new OA\Property(property: 'to', type: 'number', nullable: false),
                    new OA\Property(property: 'total', type: 'number', nullable: false),
                    new OA\Property(property: 'path', type: 'string', nullable: false),
                    new OA\Property(
                        property: 'links',
                        type: 'array',
                        items: new OA\Items(
                            ref: '#/components/schemas/MetaLinksPaginatedResource',
                            type: 'object'
                        ),
                    ),
                ],
                type: 'object',
            ),
            new OA\Schema(
                schema: 'NextSuccessHistoryResource',
                properties: [
                    new OA\Property(property: 'id', type: 'number', nullable: false),
                    new OA\Property(property: 'title', type: 'string', nullable: false),
                    new OA\Property(property: 'image', type: 'string', nullable: false),
                ],
                type: 'object',
            ),
            new OA\Schema(
                schema: 'ImagesResource',
                properties: [
                    new OA\Property(property: 'original', type: 'string', nullable: true),
                    new OA\Property(property: 'thumbnail', type: 'string', nullable: true),
                ],
                type: 'object',
            ),
            new OA\Schema(
                schema: 'SocialVideoResource',
                type: 'object',
                properties: [
                    new OA\Property(property: 'type', ref: '#/components/schemas/SocialVideoTypeEnum'),
                    new OA\Property(property: 'link', type: 'string', nullable: false),
                    new OA\Property(property: 'video_id', type: 'string', nullable: false),
                ],
            ),
        ],
        responses: [
            new OA\Response(
                response: 'error.bad_request',
                description: 'Некорректные данные запроса',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            description: 'Текст ошибки',
                            type: 'string',
                            example: 'messages.exception.bad_request',
                        ),
                        new OA\Property(
                            property: 'code', type: 'integer', default: ErrorCodes::BAD_REQUEST, nullable: false,
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: 'error.unauthorized',
                description: 'Unauthorized',
                content: new OA\JsonContent(ref: '#/components/schemas/error.unauthorized'),
            ),
            new OA\Response(
                response: 'error.forbidden',
                description: 'Forbidden',
                content: new OA\JsonContent(ref: '#/components/schemas/error.forbidden'),
            ),
            new OA\Response(
                response: 'error.too_many_requests',
                description: 'Too many requests',
                content: new OA\JsonContent(ref: '#/components/schemas/error.too_many_requests'),
            ),
            new OA\Response(
                response: 'error.server',
                description: 'Internal server error',
                content: new OA\JsonContent(ref: '#/components/schemas/error.server'),
            ),
            new OA\Response(
                response: 'error.not_found',
                description: 'Данные не найдены',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            description: 'Текст ошибки',
                            type: 'string',
                            example: 'messages.exception.not_found',
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: 'error.validation',
                description: 'Ошибка валидации запроса',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            description: 'Текст ошибки',
                            type: 'string',
                            example: 'messages.exception.given_data_invalid',
                        ),
                        new OA\Property(
                            property: 'code', type: 'integer', default: ErrorCodes::FAILED_VALIDATION, nullable: false,
                        ),
                        new OA\Property(
                            property: 'errors',
                            description: 'Текст ошибки',
                            properties: [
                                new OA\Property(
                                    property: 'field',
                                    description: 'Описание ошибки; ключ - имя соответствующего поля',
                                    type: 'string',
                                    example: 'Поле обязательно для заполнения',
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
        parameters: [
            new OA\Parameter(
                parameter: 'Page',
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'int',
                    example: 1,
                )
            ),
            new OA\Parameter(
                parameter: 'PerPage',
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'int',
                    example: 15,
                )
            ),
            new OA\Parameter(
                parameter: 'Offset',
                name: 'offset',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'int',
                    example: 0,
                )
            ),
            new OA\Parameter(
                parameter: 'Limit',
                name: 'limit',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'int',
                    example: 15,
                )
            ),
        ],
        securitySchemes: [
            new OA\SecurityScheme(
                securityScheme: 'bearer',
                type: 'apiKey',
                name: 'Authorization',
                in: 'header',
            ),
        ],
    ),
)]
abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
}
