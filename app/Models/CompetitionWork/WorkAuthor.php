<?php

declare(strict_types=1);

namespace App\Models\CompetitionWork;

use App\Models\User;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\CompetitionWork\WorkAuthor
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\WorkAuthor ageIs(int $age, string $sign = '>=')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\WorkAuthor nameLike(string $name)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\WorkAuthor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\WorkAuthor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\WorkAuthor query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\WorkAuthor whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\WorkAuthor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\WorkAuthor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\WorkAuthor whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\WorkAuthor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\WorkAuthor whereUserId($value)
 * @mixin \Eloquent
 */
final class WorkAuthor extends Model
{
    use DefaultDatetimeFormat;

    protected $fillable = [
        'user_id',
        'name',
        'birth_date',
    ];

    protected $dates = [
        'birth_date',
    ];

    public function scopeNameLike(Builder $qb, string $name): Builder
    {
        return $qb->whereRaw('lower(name) ilike ?', [\sprintf('%%%s%%', \mb_strtolower($name))]);
    }

    public function scopeAgeIs(Builder $qb, int $age, string $sign = '>='): Builder
    {
        return $qb->whereRaw(\sprintf('date_part(\'year\', age(birth_date)) %s %s', $sign, $age));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
