<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Competition\Competition;
use App\Models\MasterClass\MasterClass;
use App\Models\Traits\HasPivotJson;
use App\Models\Traits\UseVisibleStatus;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Lead
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $slug
 * @property bool $visible_status
 * @property string|null $short_description
 * @property string $description
 * @property int $order_column
 * @property string $photo
 * @property-read array|null _pivot_titles_content
 * @property-read int _pivot_order_column
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MasterClass\MasterClass[] $masterClasses
 * @property-read int|null $master_classes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Competition\Competition[] $competitions
 * @property-read int|null $competitions_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media\Media[] $media
 * @property-read int|null $media_count
 * @method static Builder|self ordered(string $direction = 'asc')
 * @method static Builder|self visible()
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereDescription($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereName($value)
 * @method static Builder|self whereOrderColumn($value)
 * @method static Builder|self whereShortDescription($value)
 * @method static Builder|self whereSlug($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereVisibleStatus($value)
 * @mixin \Eloquent
 */
final class Lead extends Model implements HasMedia, Sortable
{
    use DefaultDatetimeFormat;
    use InteractsWithMedia;
    use HasSlug;
    use SortableTrait;
    use UseVisibleStatus;
    use HasPivotJson;

    protected static ?array $pivotAdditional = Competition::ADDITIONAL_ATTRIBUTES_LEAD;

    public const PHOTO_COLLECTION = 'photo';
    public const PHOTO_THUMB_CONVERSION = 'photo_thumb';

    protected $fillable = [
        'name',
        'slug',
        'visible_status',
        'short_description',
        'description',
        'order_column',
    ];

    protected $with = [
        'media',
    ];

    protected $appends = ['photo'];

    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class)->withPivot(self::pivotAttributes());
    }

    public function masterClasses(): HasMany
    {
        return $this->hasMany(MasterClass::class);
    }

    /**
     * @return string|null
     */
    public function getPhotoAttribute(): ?string
    {
        return $this->getFirstMediaUrl(self::PHOTO_COLLECTION, self::PHOTO_THUMB_CONVERSION);
    }

    public function setPhotoAttribute(): void
    {
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::PHOTO_COLLECTION)
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
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion(self::PHOTO_THUMB_CONVERSION)
            ->fit(Manipulations::FIT_CONTAIN, 480, 500)
            ->shouldBePerformedOn(self::PHOTO_COLLECTION)
        ;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
        ;
    }
}
