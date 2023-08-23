<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\ApiRequest;
use App\Models\Location\City;
use App\Models\Location\Region;
use App\Models\Objects\PhoneNumber;
use App\Models\Objects\User\UpdateUser;
use App\Models\User;
use App\Rules\ModelExists;
use App\Rules\User\Email;
use App\Rules\User\Phone;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

/**
 * Class UpdateRequest
 * @package App\Http\Requests\User
 */
#[OA\Schema(
    schema: 'UserUpdateRequest',
    type: 'object',
    required: ['old_password'],
    properties: [
        new OA\Property(property: 'first_name', description: 'Имя', type: 'string', maxLength: 64, nullable: true),
        new OA\Property(property: 'last_name', description: 'Фамилия', type: 'string', maxLength: 64, nullable: true),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 64, nullable: true),
        new OA\Property(property: 'phone', description: 'Телефон', type: 'string', maxLength: 32, nullable: true),
        new OA\Property(property: 'birth_date', description: 'Дата рождения', type: 'string', format: 'date', example: '1990-01-31', nullable: true),
        new OA\Property(
            property: 'old_password',
            type: 'string',
            maxLength: 255,
            minLength: 6,
            nullable: false,
        ),
        new OA\Property(
            property: 'password',
            type: 'string',
            maxLength: 255,
            minLength: 6,
            nullable: true
        ),
        new OA\Property(
            property: 'password_confirmation',
            type: 'string',
            maxLength: 255,
            minLength: 6,
            nullable: true
        ),
        new OA\Property(property: 'region_id', description: 'ID региона', type: 'string', nullable: true),
        new OA\Property(property: 'city_id', description: 'ID города', type: 'string', nullable: true),
    ],
)]
final class UpdateRequest extends ApiRequest
{
    private ?Region $region = null;
    private ?City $city = null;

    public function validationData(): array
    {
        $data = parent::validationData();

        if (isset($data['phone']) && \is_string($data['phone'])) {
            $data['phone'] = Phone::clean($data['phone']);
        }

        if (isset($data['email']) && \is_string($data['email'])) {
            $data['email'] = Email::clean($data['email']);
        }

        return $data;
    }

    public function authorize(): bool
    {
        return null !== $this->user();
    }

    public function rules(): array
    {
        $rules = [
            'first_name' => ['nullable', 'string', 'max:64'],
            'last_name' => ['nullable', 'string', 'max:64'],
            'email' => [
                'nullable', 'string', 'max:64', new Email(), Rule::unique(User::class, 'email')->ignore(\Auth::id()),
            ],
            'phone' => ['bail', 'nullable', 'string', 'max:32', new Phone(), $this->validatePhone(...)],
            'birth_date' => [
                'nullable', 'date', 'date_format:Y-m-d',
                \sprintf('after:%s', \now()->subYears(150)->format('Y-m-d')),
                \sprintf('before:%s', \now()->format('Y-m-d')),
            ],
            'region_id' => [
                'bail',
                'nullable',
                'int',
                'min:1',
                ModelExists::make(Region::class)
                    ->withResultSetter(fn(Region $model) => $this->region = $model),
            ],
            'city_id' => [
                'bail',
                'required_with:region_id',
                'int',
                'min:1',
                ModelExists::make(City::class)
                    ->where(fn(Builder $query): Builder => $query->where([
                        'region_id' => $this->region?->id ?? $this->user()->region_id,
                    ]))
                    ->withResultSetter(fn(City $model) => $this->city = $model)
            ],
        ];

        if (null !== $this->user()->password) {
            $rules['old_password'] = ['required', 'string', 'min:6', 'max:255', 'current_password:api'];
            $rules['password'] = ['nullable', 'string', 'min:6', 'max:255'];
        } else {
            $rules['password'] = ['nullable', 'required_with:old_password', 'string', 'min:6', 'max:255', 'confirmed'];
        }

        return $rules;
    }

    /**
     * Если пользователь сохраняет новый телефон, то позволяем выбрать только RU формат
     *
     * @param string $attr
     * @param string|null $value
     * @param \Closure(string $message): void $fail
     *
     * @throws \Propaganistas\LaravelPhone\Exceptions\NumberParseException
     */
    public function validatePhone(string $attr, ?string $value, Closure $fail): void
    {
        if (Str::isEmpty($value)) {
            return;
        }

        $phone = PhoneNumber::make($value);

        if ($this->user()->phone === $phone->formatE164()) {
            return;
        }

        if (false === $phone->isOfCountry('RU')) {
            $fail(\__('validation.phone'));
        }
    }

    /**
     * @return \App\Models\Objects\User\UpdateUser
     *
     * @throws \Propaganistas\LaravelPhone\Exceptions\NumberParseException
     */
    public function getUpdateUser(): UpdateUser
    {
        $validated = $this->validated();

        return new UpdateUser(
            firstName: $validated['first_name'] ?? null,
            lastName: $validated['last_name'] ?? null,
            email: $validated['email'] ?? null,
            phone: isset($validated['phone']) ? PhoneNumber::make($validated['phone']) : null,
            birthDate: isset($validated['birth_date']) ? Carbon::parse($validated['birth_date']) : null,
            region: $this->region,
            city: $this->city,
            password: $validated['password'] ?? null,
        );
    }
}
