<?php

declare(strict_types=1);

namespace App\Models\Location;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class City
 *
 * @package App\Models\Location
 * @property int $id
 * @property string $title
 * @property string|null $region_id
 * @property-read \App\Models\Location\Region|null $region
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereRegionId($value)
 * @method static Builder|self whereTitle($value)
 * @mixin \Eloquent
 */
final class City extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'region_id',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
