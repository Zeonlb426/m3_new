<?php

declare(strict_types=1);

namespace App\Enums;

use OpenApi\Attributes as OA;

/**
 * Class ErrorCodes
 * @package App\Enums
 */
#[OA\Schema(
    schema: 'ErrorCodesEnum',
    description: <<<STR
Коды ошибок:
<li>40000 - bad request</li>
<li>40100 - unauthorized</li>
<li>40300 - forbidden</li>
<li>40400 - not found</li>
<li>41000 - gone</li>
<li>42200 - failed validation</li>
<li>42900 - too many requests</li>
<li>50000 - internal server error</li>
STR,
    type: 'integer',
    enum: [
        ErrorCodes::BAD_REQUEST,
        ErrorCodes::UNAUTHORIZED,
        ErrorCodes::FORBIDDEN,
        ErrorCodes::NOT_FOUND,
        ErrorCodes::GONE,
        ErrorCodes::FAILED_VALIDATION,
        ErrorCodes::TOO_MANY_REQUESTS,
        ErrorCodes::INTERNAL_SERVER_ERROR,
    ],
    nullable: false
)]
enum ErrorCodes: int
{
    case BAD_REQUEST = 40000;
    case UNAUTHORIZED = 40100;
    case FORBIDDEN = 40300;
    case NOT_FOUND = 40400;
    case GONE = 41000;
    case FAILED_VALIDATION = 42200;
    case TOO_MANY_REQUESTS = 42900;
    case INTERNAL_SERVER_ERROR = 50000;
}
