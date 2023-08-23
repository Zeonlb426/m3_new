<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Sharing
 *
 * @package App\Models
 * @property int $id
 * @property string $shared_type
 * @property string $shared_id
 * @property string $title
 * @property string $description
 * @property string|null $image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media\Media[] $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $shared
 * @method static \Illuminate\Database\Eloquent\Builder|self newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|self newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|self query()
 * @method static \Illuminate\Database\Eloquent\Builder|self whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|self whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|self whereSharedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|self whereSharedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|self whereTitle($value)
 * @mixin \Eloquent
 */
final class Sharing extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const IMAGE_COLLECTION = 'image';

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'shared_id',
        'shared_type',
    ];

    protected $with = ['media'];

    protected $appends = ['image'];

    public function shared(): MorphTo
    {
        return $this->morphTo('shared');
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
                'image/gif'
            ])
        ;
    }
}
