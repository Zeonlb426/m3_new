<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\UseVisibleStatus;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Slider
 *
 * @package App\Models
 * @property int $id
 * @property string|null $short_title
 * @property string|null $title
 * @property string $link
 * @property string|null $description
 * @property int $order_column
 * @property bool $visible_status
 * @property string|null $image
 * @property string|null $image_mobile
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media\Media[] $media
 * @property-read int|null $media_count
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self ordered(string $direction = 'asc')
 * @method static Builder|self query()
 * @method static Builder|self visible()
 * @method static Builder|self whereDescription($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereLink($value)
 * @method static Builder|self whereOrderColumn($value)
 * @method static Builder|self whereShortTitle($value)
 * @method static Builder|self whereTitle($value)
 * @method static Builder|self whereVisibleStatus($value)
 * @mixin \Eloquent
 */
final class Slider extends Model implements HasMedia, Sortable
{
    use DefaultDatetimeFormat;
    use InteractsWithMedia;
    use SortableTrait;
    use UseVisibleStatus;

    public const IMAGE_COLLECTION = 'image';
    public const IMAGE_MOBILE_COLLECTION = 'image_mobile';

    public $timestamps = false;

    protected $fillable = [
        'short_title',
        'title',
        'link',
        'description',
        'order_column',
        'visible_status',
    ];

    protected $with = [
        'media',
    ];

    protected $appends = ['image', 'image_mobile'];

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
     * @return string|null
     */
    public function getImageMobileAttribute(): ?string
    {
        return $this->getFirstMedia(self::IMAGE_MOBILE_COLLECTION)?->getUrl();
    }

    public function setImageMobileAttribute(): void
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
            ])
        ;
        $this
            ->addMediaCollection(self::IMAGE_MOBILE_COLLECTION)
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
