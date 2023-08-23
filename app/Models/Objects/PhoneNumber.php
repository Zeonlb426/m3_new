<?php

declare(strict_types=1);

namespace App\Models\Objects;

use libphonenumber\NumberParseException as NumberParseExceptionAlias;
use libphonenumber\PhoneNumberFormat as libPhoneNumberFormat;
use Propaganistas\LaravelPhone\Exceptions\NumberParseException;

final class PhoneNumber extends \Propaganistas\LaravelPhone\PhoneNumber
{
    /**
     * @return string
     *
     * @throws \Propaganistas\LaravelPhone\Exceptions\NumberFormatException
     */
    public function formatE164WithoutPlus(): string
    {
        return \str_replace('+', '', $this->format(libPhoneNumberFormat::E164));
    }

    /**
     * @throws \Propaganistas\LaravelPhone\Exceptions\NumberParseException
     */
    public static function make(string $phone): self
    {
        $phoneNumber = new self($phone);

        if (false === \in_array($phoneNumber->getCountry(), ['INTERNATIONAL', 'RU'])) {
            throw new NumberParseException(
                NumberParseExceptionAlias::INVALID_COUNTRY_CODE,
                \sprintf(
                    'Expected number for country "%s", "%s" given.',
                    'INTERNATIONAL or RU',
                    $phoneNumber->getCountry()
                )
            );
        }

        return $phoneNumber;
    }
}
