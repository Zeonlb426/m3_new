<?php

declare(strict_types=1);

namespace App\Models\Promo;

use App\Contracts\Activities\HasLikesActivityInterface;
use App\Models\Objects\VkLink;
use App\Models\Objects\YoutubeLink;
use App\Models\Traits\HasLikesActivity;
use App\Models\Traits\HasSharing;
use App\Models\Traits\UseVisibleStatus;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * App\Models\Promo\SuccessHistory
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $short_title
 * @property string $title
 * @property string|null $video_link
 * @property bool $visible_status
 * @property string $short_description
 * @property string $description
 * @property int $order_column
 * @property int $likes_total_count
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $video_type
 * @property string|null $video_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User\UserActivity> $activity
 * @property-read int|null $activity_count
 * @property string|null $image
 * @property-read mixed $next
 * @property-read array|null $video
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User\UserActivity> $likes
 * @property-read int|null $likes_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Sharing|null $sharing
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory ordered(string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory visible()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereLikesTotalCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereOrderColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereShortTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereVideoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereVideoLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereVideoType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory whereVisibleStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory withUserLikes(?\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Promo\SuccessHistory withoutTrashed()
 * @mixin \Eloquent
 */
final class SuccessHistory extends Model implements HasMedia, Sortable, HasLikesActivityInterface
{
    use DefaultDatetimeFormat;
    use InteractsWithMedia;
    use HasLikesActivity;
    use HasSharing;
    use SortableTrait;
    use UseVisibleStatus;
    use SoftDeletes;

    public const IMAGE_COLLECTION = 'image';

    protected $fillable = [
        'short_title',
        'title',
        'video_link',
        'visible_status',
        'short_description',
        'description',
        'order_column',
        'likes_total_count',
    ];

    protected $with = [
        'media',
    ];
    protected $appends = ['image'];

    /**
     * @return string|null
     */
    public function getImageAttribute(): ?string
    {
        return $this->getFirstMedia(self::IMAGE_COLLECTION)?->getUrl();
    }

    public function setImageAttribute(): void
    {
    }

    public function setVideoLinkAttribute(?string $value): void
    {
        if (null === $value) {
            return;
        }

        $video = VkLink::maybeIsVkLink($value) ? new VkLink($value) : new YoutubeLink($value);

        $this->attributes['video_type'] = $video->getVideoType()->value;
        $this->attributes['video_link'] = $video->__toString();
        $this->attributes['video_id'] = $video->getVideoId();
    }

    /**
     * @return array{type: \App\Enums\SocialVideoType::*, link: string, video_id: ?string}|null
     */
    public function getVideoAttribute(): ?array
    {
        if (null === $this->video_link) {
            return null;
        }

        return [
            'type' => $this->video_type,
            'link' => $this->video_link,
            'video_id' => $this->video_id,
        ];
    }

    /**
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::IMAGE_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp',
            ])
        ;
    }

    public function getNextAttribute()
    {
        $next = $this->where('id', '>', $this->id)->where('visible_status', true)->orderBy('id', 'asc')->first();
        if (!$next) {
            $next = $this->where('visible_status', true)->orderBy('id', 'asc')->first();
        }

        return $next;
    }

    public function targetTitle(): string
    {
        return $this->title;
    }
}
