<?php

declare(strict_types=1);

namespace App\Models\Competition;

use App\Enums\Competition\TileSize;
use App\Models\Lead;
use App\Models\Traits\HasPivotJson;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Theme
 *
 * @package App\Models\Competition
 * @property int $id
 * @property string $title
 * @property string $description
 * @property \App\Enums\Competition\TileSize $tile_size
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Competition\Competition[] $competitions
 * @property-read int|null $competitions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Competition\Competition[] $leads
 * @property-read int|null $leads_count
 * @property string|null $cover
 * @property string|null $tile
 * @property-read array|null _pivot_titles_content
 * @property-read int _pivot_order_column
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media\Media[] $media
 * @property-read int|null $media_count
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereDescription($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereTileSize($value)
 * @method static Builder|self whereTitle($value)
 * @mixin \Eloquent
 */
final class Theme extends Model implements HasMedia
{
    use DefaultDatetimeFormat;
    use InteractsWithMedia;
    use HasPivotJson;

    protected static ?array $pivotAdditional = Competition::ADDITIONAL_ATTRIBUTES_THEME;

    public $timestamps = false;

    public const COVER_COLLECTION = 'cover';
    public const TILE_COLLECTION = 'tile';

    protected $fillable = [
        'title',
        'description',
        'tile_size',
    ];

    protected $casts = [
        'tile_size' => TileSize::class
    ];

    protected $with = [
        'media',
    ];
    protected $appends = ['cover', 'tile'];

    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class)->withPivot(self::pivotAttributes());
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class);
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
}
