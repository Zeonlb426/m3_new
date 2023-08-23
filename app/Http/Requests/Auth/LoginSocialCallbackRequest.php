<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;

/**
 * Class LoginSocialCallbackRequest
 * @package App\Http\Requests\User
 */
final class LoginSocialCallbackRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
        ];
    }
}
