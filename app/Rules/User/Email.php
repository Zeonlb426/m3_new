<?php

declare(strict_types=1);

namespace App\Rules\User;

use Illuminate\Contracts\Validation\Rule;

final class Email implements Rule
{
    public static function clean(string $email): string
    {
        return \mb_strtolower($email);
    }

    public function passes($attribute, $value): bool
    {
        if (false === \is_string($value)) {
            return false;
        }

        $email = self::clean($value);

        if (
            \filter_var($email, FILTER_VALIDATE_EMAIL)
            && false === \validator(
                ['email' => $email],
                ['email' => ['required', 'string', 'email:strict,spoof', 'max:255']]
            )->fails()
        ) {
            [$addr, $domain] = \explode('@', $email);
            return 64 >= \strlen($addr) && \checkdnsrr($domain);
        }

        return false;
    }

    public function message(): string
    {
        return \__('validation.email');
    }
}
