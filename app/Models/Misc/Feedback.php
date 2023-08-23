<?php

declare(strict_types=1);

namespace App\Models\Misc;

use App\Enums\Misc\ProcessingStatus;
use App\Models\User;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Feedback
 *
 * @package App\Models\Misc
 * @property int                             $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string                          $name
 * @property string                          $email
 * @property string                          $content
 * @property ProcessingStatus                $processing_status
 * @property int|null                        $user_id
 * @property-read User|null                  $user
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereContent($value)
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereEmail($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereName($value)
 * @method static Builder|self whereProcessingStatus($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereUserId($value)
 * @mixin \Eloquent
 */
final class Feedback extends Model
{
    use DefaultDatetimeFormat;

    protected $fillable = [
        'name',
        'email',
        'content',
        'processing_status',
        'user_id',
    ];

    protected $casts = [
        'processing_status' => ProcessingStatus::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
