<?php

declare(strict_types=1);

namespace App\Models\Media;

use Encore\Admin\Traits\DefaultDatetimeFormat;

/**
 * App\Models\Media\Media
 *
 * @property int $id
 * @property string|null $uuid
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property string|null $mime_type
 * @property string $disk
 * @property string|null $conversions_disk
 * @property int $size
 * @property array $manipulations
 * @property array $custom_properties
 * @property array $generated_conversions
 * @property array $responsive_images
 * @property int|null $order_column
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $model_type
 * @property string $model_id
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $model
 * @method static \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|static[] all($columns = ['*'])
 * @method static \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|static[] get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Spatie\MediaLibrary\MediaCollections\Models\Media ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereCollectionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereConversionsDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereCustomProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereGeneratedConversions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereManipulations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereOrderColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereResponsiveImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Media\Media whereUuid($value)
 * @mixin \Eloquent
 */
final class Media extends \Spatie\MediaLibrary\MediaCollections\Models\Media
{
    use DefaultDatetimeFormat;
}
