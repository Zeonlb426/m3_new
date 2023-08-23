<?php

declare(strict_types=1);

namespace App\Models\Location;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Region
 *
 * @package App\Models\Location
 * @property int $id
 * @property string $title
 * @property string $code
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Location\City[] $cities
 * @property-read int|null $cities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCode($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereTitle($value)
 * @mixin \Eloquent
 */
final class Region extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'code',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
