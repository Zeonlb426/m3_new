<?php

declare(strict_types=1);

namespace App\Models\Objects\User;

use App\Models\Location\City;
use App\Models\Location\Region;
use App\Models\Objects\PhoneNumber;
use Carbon\Carbon;

/**
 * Class UpdateUser
 * @package App\Models\Objects\User
 */
final class UpdateUser
{
    public function __construct(
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $email,
        public readonly ?PhoneNumber $phone,
        public readonly ?Carbon $birthDate,
        public readonly ?Region $region,
        public readonly ?City $city,
        public readonly ?string $password,
    ) {
    }
}
