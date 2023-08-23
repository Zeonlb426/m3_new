<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Competition\Competition;
use App\Models\MasterClass\MasterClass;
use App\Models\Traits\HasPivotJson;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Class AgeGroup
 *
 * @package App\Models\MasterClass
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property int $min_age
 * @property int $max_age
 * @property-read bool _pivot_visible_status
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MasterClass\MasterClass[] $masterClasses
 * @property-read int|null $master_classes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Competition\Competition[] $competitions
 * @property-read int|null $competitions_count
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereMaxAge($value)
 * @method static Builder|self whereMinAge($value)
 * @method static Builder|self whereSlug($value)
 * @method static Builder|self whereTitle($value)
 * @mixin \Eloquent
 */
final class AgeGroup extends Model
{
    use DefaultDatetimeFormat;
    use HasSlug;
    use HasPivotJson;

    protected static ?array $pivotAdditional = Competition::ADDITIONAL_ATTRIBUTES_AGE_GROUP;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'slug',
        'min_age',
        'max_age',
    ];

    public function competitions(): BelongsToMany
    {
        return $this
            ->belongsToMany(Competition::class, 'competition_age_group')
            ->withPivot(self::pivotAttributes())
        ;
    }

    public function masterClasses(): HasMany
    {
        return $this->hasMany(MasterClass::class);
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
