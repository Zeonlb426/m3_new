<?php

declare(strict_types=1);

namespace App\Models\News;

use App\Contracts\Activities\HasLikesActivityInterface;
use App\Models\Objects\VkLink;
use App\Models\Objects\YoutubeLink;
use App\Models\Traits\HasLikesActivity;
use App\Models\Traits\UseVisibleStatus;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\News\News
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $title
 * @property string $slug
 * @property string|null $announce
 * @property string|null $video_link
 * @property bool $visible_status
 * @property \Illuminate\Support\Carbon $publish_date
 * @property string $content
 * @property int $likes_total_count
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $video_type
 * @property string|null $video_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User\UserActivity> $activity
 * @property-read int|null $activity_count
 * @property string|null $cover
 * @property-read string|null $thumb
 * @property-read array|null $video
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User\UserActivity> $likes
 * @property-read int|null $likes_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media\Media> $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News visible()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News whereAnnounce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News whereLikesTotalCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News wherePublishDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News whereVideoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News whereVideoLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News whereVideoType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News whereVisibleStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News withUserLikes(?\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\News\News withoutTrashed()
 * @mixin \Eloquent
 */
final class News extends Model implements HasMedia, HasLikesActivityInterface
{
    use DefaultDatetimeFormat;
    use InteractsWithMedia;
    use HasLikesActivity;
    use HasSlug;
    use UseVisibleStatus;
    use SoftDeletes;

    public const COVER_COLLECTION = 'cover';
    public const THUMB_COLLECTION = 'thumb';

    protected $table = 'news';

    protected $fillable = [
        'title',
        'slug',
        'announce',
        'video_link',
        'visible_status',
        'publish_date',
        'content',
        'likes_total_count',
    ];

    protected $casts = [
        'visible_status' => 'bool',
    ];

    protected $dates = [
        'publish_date',
    ];

    protected $with = [
        'media',
    ];

    protected $appends = ['cover'];

    /**
     * @return string|null
     */
    public function getCoverAttribute(): ?string
    {
        return $this->getFirstMedia(self::COVER_COLLECTION)?->getUrl();
    }

    /**
     * @return string|null
     */
    public function getThumbAttribute(): ?string
    {
        return $this->getFirstMedia(self::COVER_COLLECTION)?->getUrl(self::THUMB_COLLECTION);
    }

    public function setCoverAttribute(): void
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
            ->addMediaCollection(self::COVER_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp',
            ])
        ;
    }

    /**
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media|null $media
     * @return void
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion(self::THUMB_COLLECTION)
            ->performOnCollections(self::COVER_COLLECTION)
            ->width(600)
            ->height(400)
        ;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
        ;
    }

    public function targetTitle(): string
    {
        return $this->title;
    }
}
