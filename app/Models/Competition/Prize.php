<?php

declare(strict_types=1);

namespace App\Models\Competition;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Prize
 *
 * @package App\Models\Competition
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $link
 * @property int $competition_id
 * @property int|null $win_position
 * @property-read \App\Models\Competition\Competition|null $competition
 * @property string|null $image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media\Media[] $media
 * @property-read int|null $media_count
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCompetitionId($value)
 * @method static Builder|self whereDescription($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereLink($value)
 * @method static Builder|self whereTitle($value)
 * @method static Builder|self whereWinPosition($value)
 * @mixin \Eloquent
 */
final class Prize extends Model implements HasMedia
{
    use DefaultDatetimeFormat;
    use InteractsWithMedia;

    public $timestamps = false;

    public const IMAGE_COLLECTION = 'image';

    protected $fillable = [
        'title',
        'description',
        'link',
        'win_position',
        'competition_id',
    ];

    protected $with = [
        'media',
    ];
    protected $appends = ['image'];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
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
}
