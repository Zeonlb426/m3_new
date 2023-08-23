<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;
use App\Rules\User\Email;
use OpenApi\Attributes as OA;

/**
 * Class ForgotPasswordRequest
 * @package App\Http\Requests\User
 */
#[OA\Schema(
    schema: 'ForgotPasswordRequest',
    type: 'object',
    required: ['email'],
    properties: [
        new OA\Property(property: 'email', type: 'string', nullable: true),
    ],
)]
final class ForgotPasswordRequest extends ApiRequest
{
    public function validationData(): array
    {
        $data = parent::validationData();

        if (isset($data['email']) && \is_string($data['email'])) {
            $data['email'] = Email::clean($data['email']);
        }

        return $data;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'max:255', new Email()],
        ];
    }
}
