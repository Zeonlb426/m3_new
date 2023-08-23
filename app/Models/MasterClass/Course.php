<?php

declare(strict_types=1);

namespace App\Models\MasterClass;

use App\Models\Lead;
use App\Models\Traits\UseVisibleStatus;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Class Course
 *
 * @package App\Models\MasterClass
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $slug
 * @property bool $visible_status
 * @property string|null $description
 * @property int $order_column
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lead[] $leads
 * @property-read int|null $leads_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MasterClass\MasterClass[] $masterClasses
 * @property-read int|null $master_classes_count
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
 * @method static Builder|self whereSlug($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereVisibleStatus($value)
 * @mixin \Eloquent
 */
final class Course extends Model implements Sortable
{
    use DefaultDatetimeFormat;
    use HasSlug;
    use SortableTrait;
    use UseVisibleStatus;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'visible_status',
        'order_column',
    ];

    protected $casts = [
        'visible_status' => 'bool',
    ];

    public function masterClasses(): BelongsToMany
    {
        return $this->belongsToMany(MasterClass::class);
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
        ;
    }
}
