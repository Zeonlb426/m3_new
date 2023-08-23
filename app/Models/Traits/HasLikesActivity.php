<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Enums\User\ActionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property-read \Illuminate\Support\Collection<\App\Models\User\UserActivity> $likes
 * @property-read int|null $likes_count
 *
 * @method \Illuminate\Database\Eloquent\Builder|static withUserLikes(?User $user)
 *
 * @implements \App\Contracts\Activities\HasLikesActivityInterface
 *
 * @mixin \Eloquent
 */
trait HasLikesActivity
{
    use HasActivity;

    protected string $likesCountField = 'likes_total_count';

    public function likes(): MorphMany
    {
        return $this->activity()->where('action_type', ActionType::LIKE->value);
    }

    public function scopeWithUserLikes(Builder $query, ?User $user): Builder
    {
        return $query->with([
            'likes' => function (MorphMany $innerQuery) use ($user): MorphMany {
                /** @var \Illuminate\Database\Eloquent\Relations\MorphMany|\App\Models\User\UserActivity $innerQuery */
                return null !== $user ? $innerQuery->whereUserId($user->id) : $innerQuery->whereRaw('0=1');
            },
        ]);
    }

    /**
     * @see \App\Contracts\Activities\HasLikesActivityInterface::incrementLikes()
     */
    public function incrementLikes(): void
    {
        $this
            ->newQuery()
            ->whereKey($this->getKey())
            ->update([
                $this->likesCountField => \DB::raw(\sprintf('"%s" + %d', $this->likesCountField, 1)),
            ])
        ;
    }

    public function getLikesCount(): int
    {
        return $this->getAttribute($this->likesCountField);
    }
}
