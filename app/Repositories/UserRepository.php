<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\AbstractRepository;
use App\Exceptions\User\UserEmailAlreadyExistsException;
use App\Models\Objects\User\RegisterUser;
use App\Models\Objects\User\UpdateUser;
use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * Class UserRepository
 * @package App\Repositories
 *
 * @extends \App\Contracts\AbstractRepository<\App\Models\User>
 */
final class UserRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, User::class);
    }

    /**
     * @param \App\Models\Objects\User\RegisterUser $registerUser
     *
     * @return \App\Models\User
     *
     * @throws \Throwable
     */
    public function create(RegisterUser $registerUser): User
    {
        $model = $this->createModel();

        $model->first_name = $registerUser->firstName;
        $model->last_name = $registerUser->lastName;
        $model->email = $registerUser->email;
        $model->phone = $registerUser->phone->formatE164();
        $model->birth_date = $registerUser->birthDate;
        $model->password = $registerUser->password;

        $model->region()->associate($registerUser->region);
        $model->city()->associate($registerUser->city);

        $this->save($model);

        return $model;
    }

    /**
     * @param array{
     *     first_name: ?string,
     *     last_name: ?string,
     *     email: ?string,
     *     birth_date: ?\Carbon\Carbon,
     * } $attributes
     * @return \App\Models\User
     * @throws \Throwable
     */
    public function createForSocial(array $attributes): User
    {
        $attributes = \array_filter($attributes);

        $model = $this->createModel()->fill($attributes);

        $this->save($model);

        return $model;
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Objects\User\UpdateUser $updateUser
     *
     * @return \App\Models\User
     *
     * @throws \Throwable
     */
    public function update(User $user, UpdateUser $updateUser): User
    {
        $attributes = [
            'first_name' => $updateUser->firstName,
            'last_name' => $updateUser->lastName,
            'email' => $updateUser->email,
            'phone' => $updateUser->phone?->formatE164(),
            'birth_date' => $updateUser->birthDate,
            'password' => $updateUser->password,
        ];

        $attributes = \array_filter($attributes);

        $model = $user->fill($attributes);

        if (null !== $updateUser->region) {
            $user->region()->associate($updateUser->region);
        }

        if (null !== $updateUser->city) {
            $user->city()->associate($updateUser->city);
        }

        $this->save($model);

        return $model;
    }

    public function save(Model $model): bool
    {
        try {
            return $this->transaction(fn(): bool => parent::save($model));
        } catch (QueryException $exception) {
            if ($this->isUniqueConstraint($exception) && \str_contains($exception->getMessage(), 'email')) {
                throw new UserEmailAlreadyExistsException();
            }

            throw $exception;
        }
    }
}
