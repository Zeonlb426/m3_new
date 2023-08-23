<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;
use App\Models\Location\City;
use App\Models\Location\Region;
use App\Models\Objects\PhoneNumber;
use App\Models\Objects\User\RegisterUser;
use App\Models\User;
use App\Rules\ModelExists;
use App\Rules\User\Email;
use App\Rules\User\Phone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

/**
 * Class RegistrationRequest
 * @package App\Http\Requests\User
 */
#[OA\Schema(
    schema: 'RegistrationRequest',
    type: 'object',
    required: ['first_name', 'last_name', 'email', 'phone', 'birth_date', 'password', 'password_confirmation', 'region_id', 'city_id'],
    properties: [
        new OA\Property(property: 'first_name', description: 'Имя', type: 'string', maxLength: 64, nullable: false),
        new OA\Property(property: 'last_name', description: 'Фамилия', type: 'string', maxLength: 64, nullable: false),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 64, nullable: false),
        new OA\Property(property: 'phone', description: 'Телефон', type: 'string', maxLength: 32, nullable: false),
        new OA\Property(property: 'birth_date', description: 'Дата рождения', type: 'string', format: 'date', example: '1990-01-31', nullable: true),
        new OA\Property(
            property: 'password',
            type: 'string',
            maxLength: 255,
            minLength: 6,
            nullable: false
        ),
        new OA\Property(property: 'region_id', description: 'ID региона', type: 'string', nullable: false),
        new OA\Property(property: 'city_id', description: 'ID города', type: 'string', nullable: false),
    ],
)]
final class RegistrationRequest extends ApiRequest
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

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:64'],
            'last_name' => ['required', 'string', 'max:64'],
            'email' => [
                'bail', 'required', 'string', 'max:64', new Email(), Rule::unique(User::class, 'email'),
            ],
            'phone' => ['bail', 'required', 'string', 'max:32', 'phone:RU'],
            'birth_date' => [
                'required', 'date', 'date_format:Y-m-d',
                \sprintf('after:%s', \now()->subYears(150)->format('Y-m-d')),
                \sprintf('before:%s', \now()->format('Y-m-d')),
            ],
            'region_id' => [
                'bail',
                'required',
                'int',
                'min:1',
                ModelExists::make(Region::class)
                    ->withResultSetter(fn(Region $model) => $this->region = $model),
            ],
            'city_id' => [
                'bail',
                'required',
                'int',
                'min:1',
                ModelExists::make(City::class)
                    ->where(fn(Builder $query): Builder => $query->where(['region_id' => $this->region?->id]))
                    ->withResultSetter(fn(City $model) => $this->city = $model)
            ],
            'password' => ['required', 'string', 'min:6', 'max:255'],
        ];
    }

    /**
     * @return \App\Models\Objects\User\RegisterUser
     *
     * @throws \Propaganistas\LaravelPhone\Exceptions\NumberParseException
     */
    public function getRegisterUser(): RegisterUser
    {
        $validated = $this->validated();

        return new RegisterUser(
            firstName: $validated['first_name'],
            lastName: $validated['last_name'],
            email: $validated['email'],
            phone: PhoneNumber::make($validated['phone']),
            birthDate: Carbon::parse($validated['birth_date']),
            region: $this->region,
            city: $this->city,
            password: $validated['password'],
        );
    }
}
