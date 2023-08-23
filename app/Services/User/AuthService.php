<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\Auth\Token;
use App\Enums\User\SocialProvider;
use App\Exceptions\User\UserSocialAccountAlreadyExistsException;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\UserSocialRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UserSocialRepository $userSocials,
        private readonly UserService $userService,
    ) {
    }

    /**
     * @param string $email
     * @param string $password
     * @param bool $remember
     *
     * @return array{0: \App\Models\User, 1: \Laravel\Sanctum\NewAccessToken}|null
     */
    public function loginByCredentials(string $email, string $password, bool $remember = false): ?array
    {
        $user = $this->users->findOneBy(['email' => $email]);

        if (null === $user) {
            return null;
        }

        if (false === Hash::check($password, $user->password)) {
            return null;
        }

        \Auth::setUser($user);

        return [$user, $this->createAuthorizationToken($user)];
    }

    public function login(User $user): NewAccessToken
    {
        \Auth::guard()->setUser($user);

        return $this->createAuthorizationToken($user);
    }

    private function createAuthorizationToken(User $user): NewAccessToken
    {
        return $user->createToken(Token::PLAIN->value);
    }

    /**
     * @param \App\Enums\User\SocialProvider $provider
     *
     * @return array{0: \App\Models\User, 1: \Laravel\Sanctum\NewAccessToken}
     *
     * @throws \Throwable
     */
    public function loginOrRegisterSocial(SocialProvider $provider): array
    {
        $providerUser = Socialite::driver($provider->value)->user();

        $userSocial = $this->userSocials->findOneByProviderAndExternalUserId(
            $provider, (string)$providerUser->getId()
        );

        if (null === $userSocial) {
            try {
                $user = match ($provider) {
                    SocialProvider::VK => $this->makeFromVk($providerUser),
                };
            } catch (UserSocialAccountAlreadyExistsException) {
                $userSocial = $this->userSocials->findOneByProviderAndExternalUserId(
                    $provider, (string)$providerUser->getId()
                );

                $user = $userSocial->user;
            }
        } else {
            $user = $userSocial->user;
        }

        $user->load(['region', 'city']);

        return [$user, $this->login($user)];
    }

    /**
     * @param \Laravel\Socialite\Contracts\User $providerUser
     *
     * @return \App\Models\User
     *
     * @throws \Throwable
     */
    private function makeFromVk(SocialiteUser $providerUser): User
    {
        if (false === $providerUser instanceof \Laravel\Socialite\Two\User) {
            throw new InvalidArgumentException('Invalid socialite user provided.');
        }

        $user = null;

        if (Str::isNotEmpty($providerUser->email)) {
            $user = $this->users->findOneBy(['email' => $providerUser->email]);
        }

        if (null === $user) {
            $user = $this->userService->registerForSocial([
                'first_name' => $providerUser['first_name'] ?: $providerUser->name,
                'last_name' => $providerUser['last_name'] ?: $providerUser->nickname,
                'email' => $providerUser->email ?? $providerUser['email'],
                'birth_date' => $providerUser['bdate'] ? Carbon::parse($providerUser['bdate']) : null,
            ]);
        }

        $this->userSocials->create(
            $user,
            SocialProvider::VK,
            (string)$providerUser->getId(),
            $providerUser->getRaw()
        );

        return $user;
    }
}
