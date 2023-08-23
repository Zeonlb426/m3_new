<?php

declare(strict_types=1);

namespace App\Models\MasterClass;

use App\Contracts\Activities\HasLikesActivityInterface;
use App\Enums\MasterClass\AdditionalSign;
use App\Models\AgeGroup;
use App\Models\Competition\Competition;
use App\Models\Lead;
use App\Models\Objects\VkLink;
use App\Models\Objects\YoutubeLink;
use App\Models\Traits\HasLikesActivity;
use App\Models\Traits\HasPivotJson;
use App\Models\Traits\HasSharing;
use App\Models\Traits\UseVisibleStatus;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * App\Models\MasterClass\MasterClass
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $title
 * @property string|null $video_link
 * @property int $age_group_id
 * @property int|null $lead_id
 * @property array $additional_signs
 * @property bool $visible_status
 * @property string|null $content
 * @property int $order_column
 * @property int $likes_total_count
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $video_type
 * @property string|null $video_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User\UserActivity> $activity
 * @property-read int|null $activity_count
 * @property-read \App\Models\AgeGroup $ageGroup
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Competition\Competition> $competitions
 * @property-read int|null $competitions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Competition\Competition> $competitionsPreviews
 * @property-read int|null $competitions_previews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MasterClass\Course> $courses
 * @property-read int|null $courses_count
 * @property string|null $image
 * @property array $signs
 * @property-read array|null $video
 * @property-read \App\Models\Lead|null $lead
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User\UserActivity> $likes
 * @property-read int|null $likes_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Sharing|null $sharing
 * @property-read bool $_pivot_is_main
 * @property-read array|null $_pivot_titles_content
 * @property-read int $_pivot_order_column
 * @property-read P $pivot
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass ordered(string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass signHas(\App\Enums\MasterClass\AdditionalSign $signField)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass visible()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereAdditionalSigns($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereAgeGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereLikesTotalCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereOrderColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereVideoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereVideoLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereVideoType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass whereVisibleStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass withUserLikes(?\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\MasterClass\MasterClass withoutTrashed()
 * @mixin \Eloquent
 *
 * @template P of \Illuminate\Database\Eloquent\Relations\Pivot
 */
final class MasterClass extends Model implements HasMedia, Sortable, HasLikesActivityInterface
{
    use DefaultDatetimeFormat;
    use InteractsWithMedia;
    use HasLikesActivity;
    use HasSharing;
    use SortableTrait;
    use UseVisibleStatus;
    use HasPivotJson;
    use SoftDeletes;

    protected static ?array $pivotAdditional = Competition::ADDITIONAL_ATTRIBUTES_MASTER_CLASS;

    public const IMAGE_COLLECTION = 'image';

    protected $fillable = [
        'title',
        'video_link',
        'age_group_id',
        'lead_id',
        'additional_signs',
        'visible_status',
        'content',
        'likes_total_count',
    ];

    protected $casts = [
        'additional_signs' => 'json',
    ];

    protected $with = [
        'media',
    ];

    protected $appends = ['image'];

    public function scopeSignHas(Builder $qb, AdditionalSign $signField): Builder
    {
        return $qb->whereRaw(\sprintf('additional_signs::jsonb->>\'%s\' = \'true\'', $signField->value));
    }

    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class)->withPivot(self::pivotAttributes());
    }

    public function competitionsPreviews(): BelongsToMany
    {
        return $this
            ->belongsToMany(Competition::class)
            ->select(['id', 'title', 'slug'])
        ;
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class);
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

    public function getSignsAttribute(): array
    {
        $signs = [];
        foreach ($this->additional_signs as $sign => $enabled) {
            if ($enabled) {
                $signs[] = AdditionalSign::from($sign);
            }
        }

        return $signs;
    }

    public function setSignsAttribute(?array $newValues = []): void
    {
        $signs = [];
        foreach (AdditionalSign::cases() as $sign) {
            $signs[$sign->value] = \in_array($sign->value, $newValues);
        }
        $this->additional_signs = $signs;
    }

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
                'image/gif',
            ])
        ;
    }

    public function targetTitle(): string
    {
        return $this->title;
    }
}
