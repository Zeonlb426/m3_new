<?php

declare(strict_types=1);

namespace App\Models\Competition;

use App\Models\Traits\UseVisibleStatus;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Class WorkType
 *
 * @package App\Models\Competition
 * @property int $id
 * @property string $title
 * @property string|null $slug
 * @property mixed|null $formats
 * @property bool $visible_status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Competition\Competition> $competitions
 * @property-read int|null $competitions_count
 * @method static Builder|self slugLike(string $slug)
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self visible()
 * @method static Builder|self whereFormats($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereTitle($value)
 * @method static Builder|self whereVisibleStatus($value)
 * @mixin \Eloquent
 */
final class WorkType extends Model
{
    use DefaultDatetimeFormat;
    use HasSlug;
    use UseVisibleStatus;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'slug',
        'formats',
        'visible_status',
    ];

    protected $casts = [
        'formats' => 'json',
    ];

    public function scopeSlugLike(Builder $qb, string $slug): Builder
    {
        return $qb->whereRaw('lower(slug) like ?', [\sprintf('%%%s%%', $slug)]);
    }

    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
        ;
    }
}
