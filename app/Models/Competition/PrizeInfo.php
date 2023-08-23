<?php

declare(strict_types=1);

namespace App\Models\Competition;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PrizeInfo
 *
 * @package App\Models\Competition
 * @property int $id
 * @property array{like_text: string, gift_text: string}|null $titles_content
 * @property int $competition_id
 * @property-read \App\Models\Competition\Competition $competition
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCompetitionId($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereTitlesContent($value)
 * @mixin \Eloquent
 */
final class PrizeInfo extends Model
{
    public $timestamps = false;

    const ADDITIONAL_FIELDS = [
        'like_text' => 'string',
        'gift_text' => 'string',
    ];

    protected $fillable = [
        'titles_content',
        'competition_id',
    ];

    protected $casts = [
        'titles_content' => 'json',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }
}
