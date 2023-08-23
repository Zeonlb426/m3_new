<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;

/**
 * Class PasswordService
 * @package App\Services
 */
final class PasswordService
{
    public function __construct(
        private readonly UserRepository $users,
    ) {}

    /**
     * @param string $email
     * @return bool|string
     */
    public function sendForgot(string $email): bool|string
    {
        $user = $this->users->findOneBy(['email' => $email]);

        if (null !== $user) {
            $status = Password::sendResetLink(['email' => $email]);
            if ($status !== Password::RESET_LINK_SENT) {
                return $status;
            }
        }

        return true;
    }

    /**
     * @param string $email
     * @param string $token
     * @param string $password
     * @return bool|string
     */
    public function doReset(string $email, string $token, string $password): bool|string
    {
        $users = $this->users;
        $status = Password::reset(
            [
                'token' => $token,
                'email' => $email,
                'password' => $password
            ],
            function (User $user, string $password) use ($users) {
                $user->password = $password;
                $user->setRememberToken($user::generateRememberToken());
                $users->save($user);

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $status;
        }

        return true;
    }
}
