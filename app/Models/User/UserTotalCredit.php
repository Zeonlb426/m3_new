<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\User\UserTotalCredit
 *
 * @property int $id
 * @property int $user_id
 * @property int $count_total
 * @property int $count_register
 * @property int $count_likes
 * @property int $count_works
 * @property-read \App\Models\User $user
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCountLikes($value)
 * @method static Builder|self whereCountRegister($value)
 * @method static Builder|self whereCountTotal($value)
 * @method static Builder|self whereCountWorks($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereUserId($value)
 * @mixin \Eloquent
 */
final class UserTotalCredit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'count_total',
        'count_register',
        'count_likes',
        'count_works',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
