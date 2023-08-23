<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserAvatarUpdateRequest',
    type: 'object',
    properties: [
        new OA\Property(property: 'avatar', type: 'string', format: 'binary', nullable: false, description: 'Аватар'),
    ],
)]
final class UserAvatarUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'max:' . (5 * 1024)],
        ];
    }
}
