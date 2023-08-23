<?php

declare(strict_types=1);

namespace App\Models\Competition;

use App\Enums\Competition\TileSize;
use App\Models\AgeGroup;
use App\Models\Lead;
use App\Models\MasterClass\MasterClass;
use App\Models\Objects\Competition\TitlesContent;
use App\Models\Traits\HasSharing;
use App\Models\Traits\UseVisibleStatus;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Competition\Competition
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $title
 * @property string $slug
 * @property string|null $period
 * @property string|null $content
 * @property string $short_content
 * @property \App\Models\Objects\Competition\TitlesContent $titles_content
 * @property \App\Enums\Competition\TileSize $tile_size
 * @property bool $visible_status
 * @property int $order_column
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AgeGroup> $ageGroups
 * @property-read int|null $age_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AgeGroup> $ageGroupsAll
 * @property-read int|null $age_groups_all_count
 * @property string|null $cover
 * @property string|null $tile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lead> $leads
 * @property-read int|null $leads_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lead> $leadsAll
 * @property-read int|null $leads_all_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MasterClass\MasterClass> $masterClasses
 * @property-read int|null $master_classes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MasterClass\MasterClass> $masterClassesAll
 * @property-read int|null $master_classes_all_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Competition\Partner> $partners
 * @property-read int|null $partners_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Competition\Partner> $partnersAll
 * @property-read int|null $partners_all_count
 * @property-read \App\Models\Competition\PrizeInfo|null $prizeInfo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Competition\Prize> $prizes
 * @property-read int|null $prizes_count
 * @property-read \App\Models\Sharing|null $sharing
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Competition\Theme> $themes
 * @property-read int|null $themes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Competition\WorkType> $workTypes
 * @property-read int|null $work_types_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Competition\WorkType> $workTypesAll
 * @property-read int|null $work_types_all_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition ordered(string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition visible()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition whereOrderColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition whereShortContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition whereTileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition whereTitlesContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\Competition whereVisibleStatus($value)
 * @mixin \Eloquent
 */
final class Competition extends Model implements HasMedia, Sortable
{
    use DefaultDatetimeFormat;
    use InteractsWithMedia;
    use HasSharing;
    use HasSlug;
    use SortableTrait;
    use UseVisibleStatus;

    public const COVER_COLLECTION = 'cover';
    public const TILE_COLLECTION = 'tile';

    public const ADDITIONAL_FIELDS = [
        'section_name' => [
            'lead' => 'string',
            'master-class' => 'string',
            'partner' => 'string',
            'theme' => 'string',
        ],
        'section_enabled' => [
            'lead' => 'bool',
            'master-class' => 'bool',
            'partner' => 'bool',
            'theme' => 'bool',
        ],
        'like_text' => 'string',
        'add_work_enabled' => 'bool',
        'add_work_text' => 'string',
        'works_enabled' => 'bool',
        'works_filtration_enabled' => 'bool',
    ];

    public const ADDITIONAL_ATTRIBUTES_AGE_GROUP = [
        'visible_status' => 'bool',
    ];
    public const ADDITIONAL_ATTRIBUTES_THEME = [
        'titles_content' => 'json',
        'order_column' => 'int',
    ];
    public const ADDITIONAL_ATTRIBUTES_LEAD = [
        'titles_content' => 'json',
        'order_column' => 'int',
    ];
    public const ADDITIONAL_ATTRIBUTES_MASTER_CLASS = [
        'titles_content' => 'json',
        'order_column' => 'int',
        'is_main' => 'bool',
        'theme_ids' => 'json',
    ];
    public const ADDITIONAL_ATTRIBUTES_PARTNER = [
        'titles_content' => 'json',
        'order_column' => 'int',
        'is_main' => 'bool',
    ];

    protected $fillable = [
        'title',
        'slug',
        'period',
        'short_content',
        'content',
        'titles_content',
        'tile_size',
        'visible_status',
        'order_column',
    ];

    protected $casts = [
        'tile_size' => TileSize::class,
        'titles_content' => TitlesContent::class . ':default',
    ];

    protected $with = [
        'media',
    ];

    protected $appends = ['cover', 'tile'];

    public function ageGroups(): BelongsToMany
    {
        return $this
            ->belongsToMany(AgeGroup::class, 'competition_age_group')
            ->withPivot(AgeGroup::pivotAttributes())
            ->orderBy('min_age')->orderBy('max_age')
            ->wherePivot('visible_status', true)
        ;
    }

    public function ageGroupsAll(): BelongsToMany
    {
        return $this
            ->belongsToMany(AgeGroup::class, 'competition_age_group')
            ->withPivot(AgeGroup::pivotAttributes())
            ->orderBy('min_age')->orderBy('max_age')
        ;
    }

    public function themes(): BelongsToMany
    {
        return $this
            ->belongsToMany(Theme::class)
            ->withPivot(Theme::pivotAttributes())
            ->orderByPivot('order_column')
        ;
    }

    public function leads(): BelongsToMany
    {
        return $this
            ->belongsToMany(Lead::class)
            ->visible()
            ->withPivot(Lead::pivotAttributes())
            ->orderByPivot('order_column')
        ;
    }

    public function leadsAll(): BelongsToMany
    {
        return $this
            ->belongsToMany(Lead::class)
            ->withPivot(Lead::pivotAttributes())
            ->orderByPivot('order_column')
        ;
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(Prize::class)->orderBy('win_position');
    }

    public function prizeInfo(): HasOne
    {
        return $this->hasOne(PrizeInfo::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|\App\Models\MasterClass\MasterClass
     */
    public function masterClasses(): BelongsToMany
    {
        return $this
            ->belongsToMany(MasterClass::class, CompetitionMasterClass::class)
            ->visible()
            ->withoutTrashed()
            ->withPivot(MasterClass::pivotAttributes())
            ->orderByPivot('is_main', 'desc')
            ->orderByPivot('master_class_id', 'desc')
        ;
    }

    public function masterClassesAll(): BelongsToMany
    {
        return $this
            ->belongsToMany(MasterClass::class, CompetitionMasterClass::class)
            ->withoutTrashed()
            ->withPivot(MasterClass::pivotAttributes())
            ->orderByPivot('is_main', 'desc')
            ->orderByPivot('master_class_id', 'desc')
        ;
    }

    public function partners(): BelongsToMany
    {
        return $this
            ->belongsToMany(Partner::class)
            ->visible()
            ->withPivot(Partner::pivotAttributes())
            ->orderByPivot('is_main', 'desc')
            ->orderByPivot('order_column')
        ;
    }

    public function partnersAll(): BelongsToMany
    {
        return $this
            ->belongsToMany(Partner::class)
            ->withPivot(Partner::pivotAttributes())
            ->orderByPivot('is_main', 'desc')
            ->orderByPivot('order_column')
        ;
    }

    public function workTypes(): BelongsToMany
    {
        return $this->belongsToMany(WorkType::class)->visible();
    }

    public function workTypesAll(): BelongsToMany
    {
        return $this->belongsToMany(WorkType::class);
    }

    /**
     * @return string|null
     */
    public function getCoverAttribute(): ?string
    {
        return $this->getFirstMedia(self::COVER_COLLECTION)?->getUrl();
    }

    public function setCoverAttribute(): void
    {
    }

    /**
     * @return string|null
     */
    public function getTileAttribute(): ?string
    {
        return $this->getFirstMedia(self::TILE_COLLECTION)?->getUrl();
    }

    public function setTileAttribute(): void
    {
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
        $this
            ->addMediaCollection(self::TILE_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp',
            ])
        ;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function (self $model): string {
                return \strip_tags($model->title);
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
        ;
    }
}
