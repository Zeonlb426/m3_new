<?php

declare(strict_types=1);

namespace App\Models\CompetitionWork;

use App\Contracts\Activities\HasLikesActivityInterface;
use App\Enums\Competition\WorkContentType;
use App\Enums\Competition\WorkTypeSlug;
use App\Enums\CompetitionWork\ApproveStatus;
use App\Models\Competition\Competition;
use App\Models\Competition\Theme;
use App\Models\Competition\WorkType;
use App\Models\Media\Media;
use App\Models\Relation\HasManyWorkPreviewMedia;
use App\Models\Traits\HasLikesActivity;
use App\Models\Traits\UseVisibleStatus;
use App\Models\User;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * App\Models\CompetitionWork\Work
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\Enums\CompetitionWork\ApproveStatus $status
 * @property int $order_column
 * @property int $likes_total_count
 * @property int $user_id
 * @property int $author_id
 * @property int $competition_id
 * @property int|null $theme_id
 * @property int $work_type_id
 * @property string|null $work_video
 * @property string|null $work_text
 * @property bool $has_content
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $work_video_type
 * @property string|null $work_video_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User\UserActivity> $activity
 * @property-read int|null $activity_count
 * @property-read \App\Models\CompetitionWork\WorkAuthor $author
 * @property-read \App\Models\Competition\Competition $competition
 * @property-read array|null $content
 * @property-read string|null $preview_url
 * @property string|null $work_audio
 * @property string|null $work_image
 * @property array $work_images
 * @property-read array $work_video_content
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User\UserActivity> $likes
 * @property-read int|null $likes_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media\Media> $media
 * @property-read int|null $media_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media\Media> $previewMedia
 * @property-read int|null $preview_media_count
 * @property-read \App\Models\Competition\Theme|null $theme
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Competition\WorkType $workType
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work ordered(string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work visible()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereCompetitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereLikesTotalCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereOrderColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereThemeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereWorkText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereWorkTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereWorkVideo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereWorkVideoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work whereWorkVideoType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work withUserLikes(?\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CompetitionWork\Work withoutTrashed()
 * @mixin \Eloquent
 */
final class Work extends Model implements HasMedia, Sortable, HasLikesActivityInterface
{
    use DefaultDatetimeFormat;
    use InteractsWithMedia;
    use HasLikesActivity;
    use SortableTrait;
    use UseVisibleStatus;
    use SoftDeletes;

    public const PREVIEW_COLLECTION = 'preview';
    public const IMAGE_COLLECTION = 'work_image';
    public const AUDIO_COLLECTION = 'work_audio';

    protected $fillable = [
        'user_id',
        'author_id',
        'competition_id',
        'theme_id',
        'work_type_id',
        'status',
        'order_column',
        'likes_total_count',
        'work_video',
        'work_text',
        'has_content',
    ];

    protected $casts = [
        'status' => ApproveStatus::class,
    ];

    protected $with = [
        'workType',
        'media',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function workType(): BelongsTo
    {
        return $this->belongsTo(WorkType::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(WorkAuthor::class);
    }

    public function previewMedia(): HasManyWorkPreviewMedia
    {
        return new HasManyWorkPreviewMedia($this);
    }

    public function getPreviewUrlAttribute(): ?string
    {
        $previewUrl = $this->getFirstMediaUrl(self::PREVIEW_COLLECTION);

        if (Str::isNotEmpty($previewUrl)) {
            return $previewUrl;
        }

        $medias = $this->previewMedia
            ->filter(fn(Media $media): bool => (new Theme)->getMorphClass() === $media->model_type)
            ->values()
        ;

        if ($medias->isNotEmpty()) {
            $medias = $medias->groupBy('collection_name');

            return $medias
                ->get(Theme::TILE_COLLECTION, $medias->get(Theme::COVER_COLLECTION))
                ->first()
                ->getUrl()
            ;
        }

        $medias = $this->previewMedia
            ->filter(fn(Media $media): bool => (new Competition)->getMorphClass() === $media->model_type)
            ->values()
            ->groupBy('collection_name')
        ;

        return $medias
            ->get(Competition::TILE_COLLECTION, $medias->get(Competition::COVER_COLLECTION))
            ?->first()
            ?->getUrl()
        ;
    }

    public function scopeVisible(Builder $query): Builder
    {
        /* @var $query self */
        return $query->whereStatus(ApproveStatus::APPROVED->value);
    }

    public function getContentAttribute(): ?array
    {
        $slug = WorkTypeSlug::tryFrom(\strtolower($this->workType->slug));

        $content = [];

        foreach (($slug?->allowedContentTypes() ?? []) as $allowedContentType) {
            $content[$allowedContentType->value] = match ($allowedContentType) {
                WorkContentType::AUDIO => $this->work_audio,
                WorkContentType::VIDEO => $this->work_video_content,
                WorkContentType::TEXT => $this->work_text,
                WorkContentType::IMAGE => $this->work_image,
                WorkContentType::IMAGES => $this->work_images,
            };
        }

        return \count($content) > 0 ? $content : null;
    }

    public function getWorkImageAttribute(): ?string
    {
        return $this->getFirstMediaUrl(self::IMAGE_COLLECTION);
    }

    public function setWorkImageAttribute(): void
    {
    }

    public function getWorkImagesAttribute(): array
    {
        return $this
            ->getMedia(self::IMAGE_COLLECTION)
            ->map(fn(Media $media) => $media->getUrl())
            ->toArray()
        ;
    }

    public function setWorkImagesAttribute(): void
    {
    }

    public function getWorkAudioAttribute(): ?string
    {
        return $this->getFirstMediaUrl(self::AUDIO_COLLECTION);
    }

    public function setWorkAudioAttribute(): void
    {
    }

    /**
     * @return array{type: \App\Enums\SocialVideoType::*, link: string, video_id: ?string}|null
     */
    public function getWorkVideoContentAttribute(): ?array
    {
        $workTypeSlug = WorkTypeSlug::tryFrom($this->workType?->slug);

        if (false === (bool)$workTypeSlug?->isAllowedContentTypes(WorkContentType::VIDEO)) {
            return null;
        }

        if (null === $this->work_video_type) {
            return null;
        }

        return [
            'type' => $this->work_video_type,
            'link' => $this->work_video,
            'video_id' => $this->work_video_id,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::PREVIEW_COLLECTION)->singleFile();

        $this
            ->addMediaCollection(self::IMAGE_COLLECTION)
            ->acceptsMimeTypes([
                'image/bmp',
                'image/jpg', 'image/jpeg',
                'image/tiff', 'image/tiff-fx',
                'image/png',
                'image/gif',
                'image/webp',
                'image/x-ms-bmp'
            ])
        ;

        $this
            ->addMediaCollection(self::AUDIO_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes([
                'audio/wav', 'audio/wave', 'audio/x-wav',
                'audio/mpeg3', 'audio/mp3', 'audio/mp4', 'audio/m4a', 'audio/x-m4a', 'audio/x-mp3',
                'audio/amr', 'audio/amr-wb', 'audio/amr-wb+', 'audio/x-amr-wb+',
                'audio/3gp', 'audio/3gpp', 'audio/vnd.3gpp.iufp',
                'audio/aac', 'audio/aacp', 'audio/x-aac',
                'audio/ogg',
                'audio/webm',
                'audio/mpeg',
                'video/x-ms-asf',
                'application/octet-stream',
                'audio/octet-stream'
            ])
        ;
    }

    public function targetTitle(): string
    {
        return 'Work #' . $this->getKey();
    }

    public function toArray(): array
    {
        return \array_merge(parent::toArray(), ['content' => $this->content]);
    }
}
