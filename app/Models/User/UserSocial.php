<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Enums\User\SocialProvider;
use App\Models\User;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserSocial
 *
 * @package App\Models
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $user_id
 * @property \App\Enums\User\SocialProvider $provider
 * @property string $external_user_id
 * @property array $raw_data
 * @property-read \App\Models\User $user
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereExternalUserId($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereProvider($value)
 * @method static Builder|self whereRawData($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereUserId($value)
 * @mixin \Eloquent
 */
final class UserSocial extends Model
{
    use DefaultDatetimeFormat;

    public $fillable = [
        'user_id',
        'provider',
        'external_user_id',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'provider' => SocialProvider::class,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
