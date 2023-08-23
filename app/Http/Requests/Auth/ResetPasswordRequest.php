<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;
use App\Rules\User\Email;
use OpenApi\Attributes as OA;

/**
 * Class ResetPasswordRequest
 * @package App\Http\Requests\User
 */
#[OA\Schema(
    schema: 'ResetPasswordRequest',
    required: ['token', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'token', type: 'string', nullable: false),
        new OA\Property(property: 'email', type: 'string', nullable: false),
        new OA\Property(
            property: 'password',
            type: 'string',
            maxLength: 255,
            minLength: 6,
            nullable: false
        ),
        new OA\Property(
            property: 'password_confirmation',
            type: 'string',
            maxLength: 255,
            minLength: 6,
            nullable: false
        ),
    ],
    type: 'object',
)]
final class ResetPasswordRequest extends ApiRequest
{
    public function validationData(): array
    {
        $data = parent::validationData();

        $data['email'] && $data['email'] = Email::clean($data['email']);

        return $data;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', new Email()],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
        ];
    }
}
