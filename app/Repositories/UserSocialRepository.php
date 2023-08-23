<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\AbstractRepository;
use App\Enums\User\SocialProvider;
use App\Exceptions\User\UserSocialAccountAlreadyExistsException;
use App\Models\User;
use App\Models\User\UserSocial;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * Class UserSocialRepository
 * @package App\Repositories
 *
 * @extends \App\Contracts\AbstractRepository<UserSocial>
 */
final class UserSocialRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, UserSocial::class);
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Enums\User\SocialProvider $provider
     * @param string $external
     * @param array|null $data
     *
     * @return \App\Models\User\UserSocial
     *
     * @throws \Throwable
     */
    public function create(User $user, SocialProvider $provider, string $external, ?array $data): UserSocial
    {
        $model = $this->createModel();

        $model->provider = $provider;
        $model->external_user_id = $external;
        $model->raw_data = $data;

        $model->user()->associate($user);

        $this->save($model);

        return $model;
    }

    public function findOneByProviderAndExternalUserId(SocialProvider $provider, string $externalUserId): ?UserSocial
    {
        return $this->createQuery()
            ->where([
                'provider' => $provider->value,
                'external_user_id' => $externalUserId,
            ])
            ->first()
        ;
    }

    public function save(Model $model): bool
    {
        try {
            return $this->transaction(fn(): bool => parent::save($model));
        } catch (QueryException $exception) {
            if ($this->isUniqueConstraint($exception)
                && \str_contains($exception->getMessage(), 'user_socials_provider_external_user_id_unique')
            ) {
                throw new UserSocialAccountAlreadyExistsException();
            }

            throw $exception;
        }
    }
}
