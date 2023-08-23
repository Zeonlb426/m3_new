<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Activities\HasActivitiesInterface;
use App\Enums\User\ActionType;
use App\Models\CompetitionWork\Work;
use App\Models\CompetitionWork\WorkAuthor;
use App\Models\Location\City;
use App\Models\Location\Region;
use App\Models\Traits\HasActivity;
use App\Models\User\UserActivity;
use App\Models\User\UserSocial;
use App\Models\User\UserTotalCredit;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * App\Models\User
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property string|null $password
 * @property int|null $region_id
 * @property int|null $city_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User\UserActivity> $activity
 * @property-read int|null $activity_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CompetitionWork\Work> $works
 * @property-read int|null $works_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CompetitionWork\WorkAuthor> $authors
 * @property-read int|null $authors_count
 * @property-read \App\Models\Location\City|null $city
 * @property string|null $avatar
 * @property-read string $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User\UserActivity> $likes
 * @property-read int|null $likes_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Location\Region|null $region
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User\UserSocial> $userSocial
 * @property-read int|null $user_social_count
 * @property-read \App\Models\User\UserTotalCredit|null $totalCredit
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User nameLike(string $name)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class User extends Authenticatable implements HasMedia, HasActivitiesInterface
{
    use DefaultDatetimeFormat;
    use InteractsWithMedia;
    use Notifiable;
    use HasApiTokens;
    use HasActivity;

    public const AVATAR_COLLECTION = 'avatar';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
        'password',
        'region_id',
        'city_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = [
        'birth_date',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = [
        'media'
    ];

    protected $appends = ['avatar'];

    public function scopeNameLike(Builder $qb, string $name): Builder
    {
        return $qb->where(
            fn($builder) => /* @var $builder \Illuminate\Database\Eloquent\Builder */
            $builder
                ->orWhereRaw('concat(first_name, \' \', last_name) ilike ?', ["%$name%"])
                ->orWhereRaw('concat(last_name, \' \', first_name) ilike ?', ["%$name%"])
        );
    }

    public function getNameAttribute(): string
    {
        return \trim(\sprintf('%s %s', $this->first_name, $this->last_name)) ?: \__('admin.messages.empty_value');
    }

    public function totalCredit(): HasOne
    {
        return $this->hasOne(UserTotalCredit::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function authors(): HasMany
    {
        return $this->hasMany(WorkAuthor::class);
    }

    public function likes(): HasMany
    {
        return $this->activity()->where('action_type', ActionType::LIKE->value);
    }

    public function activity(): HasMany
    {
        return $this->hasMany(UserActivity::class)->orderByDesc('created_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\App\Models\CompetitionWork\Work
     */
    public function works(): HasMany
    {
        return $this->hasMany(Work::class)->withoutTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userSocial(): HasMany
    {
        return $this->hasMany(UserSocial::class);
    }

    /**
     * @return string|null
     */
    public function getAvatarAttribute(): ?string
    {
        return $this->getFirstMediaUrl(self::AVATAR_COLLECTION);
    }

    public function setAvatarAttribute(): void
    {
    }

    public function setPasswordAttribute($value): void
    {
        $password = null;

        if (false === empty($value)) {
            if (null === @Hash::info($value)['algo']) {
                $password = Hash::make($value);
            } else {
                $password = $value;
            }
        }

        if (null !== $password) {
            $this->attributes['password'] = $password;
        } else {
            $this->attributes['password'] = null;
            $this->original['password'] = null;
        }
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::AVATAR_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp',
                'image/gif',
            ])
        ;
    }

    public function targetTitle(): string
    {
        return $this->name;
    }

    public static function generateRememberToken(): string
    {
        return \Str::random(60);
    }
}
