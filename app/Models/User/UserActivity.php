<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Enums\User\ActionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\User\UserActivity
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\Enums\User\ActionType $action_type
 * @property string $interacted_type
 * @property int $interacted_id
 * @property int $user_id
 * @property int $point
 * @property int $credits
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $interacted
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity whereActionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity whereCredits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity whereInteractedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity whereInteractedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity wherePoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User\UserActivity withoutTrashed()
 * @mixin \Eloquent
 */
final class UserActivity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'action_type',
        'user_id',
        'interacted_id',
        'interacted_type',
        'point',
        'credits',
    ];

    protected $casts = [
        'action_type' => ActionType::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function interacted(): MorphTo
    {
        return $this->morphTo('interacted');
    }
}
