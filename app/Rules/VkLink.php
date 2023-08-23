<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Objects\VkLink as VkLinkObject;
use Illuminate\Contracts\Validation\Rule;
use InvalidArgumentException;

final class VkLink implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        try {
            return null !== VkLinkObject::tryParseUri($value);
        } catch (InvalidArgumentException) {
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return \__('validation.vk');
    }
}
