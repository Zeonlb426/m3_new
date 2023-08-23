<?php

declare(strict_types=1);

namespace App\Models\Competition;

use App\Models\Traits\HasPivotJson;
use App\Models\Traits\UseVisibleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Partner
 *
 * @package App\Models\Competition
 * @property int $id
 * @property string $title
 * @property string $link
 * @property string $description
 * @property bool $visible_status
 * @property string|null $logo
 * @property string|null $background
 * @property string|null $slider
 * @property-read array|null $_pivot_titles_content
 * @property-read int $_pivot_order_column
 * @property-read bool $_pivot_is_main
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media\Media[] $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Competition\Competition[] $competitions
 * @property-read int|null $competitions_count
 * @method static Builder|self visible()
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereDescription($value)
 * @method static Builder|self whereLink($value)
 * @method static Builder|self whereTitle($value)
 * @method static Builder|self whereVisibleStatus($value)
 * @mixin \Eloquent
 */
final class Partner extends Model implements HasMedia
{
    use InteractsWithMedia;
    use UseVisibleStatus;
    use HasPivotJson;

    protected static ?array $pivotAdditional = Competition::ADDITIONAL_ATTRIBUTES_PARTNER;

    public const LOGO_COLLECTION = 'logo';
    public const SLIDER_COLLECTION = 'slider';
    public const BACKGROUND_COLLECTION = 'background';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'link',
        'description',
        'visible_status',
    ];

    protected $with = [
        'media',
    ];

    protected $appends = ['logo', 'background', 'slider'];

    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class)->withPivot(self::pivotAttributes());
    }

    /**
     * @return string|null
     */
    public function getLogoAttribute(): ?string
    {
        return $this->getFirstMedia(self::LOGO_COLLECTION)?->getUrl();
    }

    public function setLogoAttribute(): void
    {
    }

    /**
     * @return string|null
     */
    public function getBackgroundAttribute(): ?string
    {
        return $this->getFirstMedia(self::BACKGROUND_COLLECTION)?->getUrl();
    }

    public function setBackgroundAttribute(): void
    {
    }

    /**
     * @return string|null
     */
    public function getSliderAttribute(): ?string
    {
        return $this->getFirstMedia(self::SLIDER_COLLECTION)?->getUrl();
    }

    public function setSliderAttribute(): void
    {
    }

    /**
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::LOGO_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                'image/webp',
            ])
        ;
        $this
            ->addMediaCollection(self::BACKGROUND_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                'image/webp',
            ])
        ;
        $this
            ->addMediaCollection(self::SLIDER_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                'image/webp',
            ])
        ;
    }
}
