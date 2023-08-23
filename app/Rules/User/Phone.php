<?php

declare(strict_types=1);

namespace App\Rules\User;

use Illuminate\Contracts\Validation\Rule;

final class Phone implements Rule
{
    public static function clean(string $phone, bool $nullOnInvalid = false): ?string
    {
        $clearPhone = \sprintf('+7%s', \preg_replace(['/^\+?[78]?/', '/\D+/'], ['', ''], $phone));
        if (\phone($clearPhone, ['INTERNATIONAL', 'RU'])->isValid()) {
            return $clearPhone;
        }
        return $nullOnInvalid ? null : $clearPhone;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return \phone($value, ['INTERNATIONAL', 'RU'])->isValid();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return \__('validation.phone');
    }
}
