<?php

declare(strict_types=1);

namespace App\Models\Misc;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Class Document
 *
 * @package App\Models\Misc
 * @property int $id
 * @property string $name
 * @property string $file_name
 * @property string $slug
 * @property string|null $file
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media\Media[] $media
 * @property-read int|null $media_count
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereFileName($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereName($value)
 * @method static Builder|self whereSlug($value)
 * @mixin \Eloquent
 */
final class Document extends Model implements HasMedia
{
    use DefaultDatetimeFormat;
    use HasSlug;
    use InteractsWithMedia;

    public const FILE_COLLECTION = 'file';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'file_name',
        'slug',
    ];
    protected $with = [
        'media',
    ];

    protected $appends = ['file'];

    /**
     * @return string|null
     */
    public function getFileAttribute(): ?string
    {
        return $this->getFirstMedia(self::FILE_COLLECTION)?->getUrl();
    }

    public function setFileAttribute(): void
    {
    }

    /**
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::FILE_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])
        ;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
        ;
    }
}
