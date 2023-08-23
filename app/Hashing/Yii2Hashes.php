<?php

declare(strict_types=1);

namespace App\Hashing;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Hashing\AbstractHasher;

final class Yii2Hashes extends AbstractHasher implements Hasher
{
    public function check($value, $hashedValue, array $options = []): bool
    {
        if (\is_null($hashedValue) || \strlen($hashedValue) === 0) {
            return false;
        }

        return \password_verify($value, $hashedValue);
    }

    public function make($value, array $options = []): string
    {
        return \password_hash($value, PASSWORD_DEFAULT, $options);
    }

    public function needsRehash($hashedValue, array $options = []): bool
    {
        return \password_needs_rehash($hashedValue, PASSWORD_DEFAULT, $options);
    }
}
