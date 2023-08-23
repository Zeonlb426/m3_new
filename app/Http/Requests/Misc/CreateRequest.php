<?php

declare(strict_types = 1);

namespace App\Http\Requests\Misc;

use App\Http\Requests\ApiRequest;
use App\Rules\ReCaptchaRule;
use App\Rules\User\Email;
use OpenApi\Attributes as OA;

/**
 * Class CreateRequest
 * @package App\Http\Requests\Misc
 */
#[OA\Schema(
    schema: 'FeedbackCreateRequest',
    required: ['name', 'email', 'content', 'recaptcha_token'],
    properties: [
        new OA\Property(property: 'name', description: 'ФИО', type: 'string', nullable: false),
        new OA\Property(property: 'email', type: 'string', format: 'email', nullable: false),
        new OA\Property(property: 'content', type: 'string', maxLength: 2147483647, nullable: false),
        new OA\Property(property: 'recaptcha_token', type: 'string', maxLength: 1024, nullable: false),
    ],
    type: 'object',
)]
final class CreateRequest extends ApiRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', new Email()],
            'content' => ['required', 'string', 'max:' . (2**31)],
            'recaptcha_token' => ['required', new ReCaptchaRule($this->recaptcha_token)],
        ];
    }
}
