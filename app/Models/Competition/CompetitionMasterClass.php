<?php

declare(strict_types=1);

namespace App\Models\Competition;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\Competition\CompetitionMasterClass
 *
 * @property int $master_class_id
 * @property int $competition_id
 * @property array|null $titles_content
 * @property bool $is_main
 * @property int $order_column
 * @property array $theme_ids
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\CompetitionMasterClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\CompetitionMasterClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\CompetitionMasterClass query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\CompetitionMasterClass whereCompetitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\CompetitionMasterClass whereIsMain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\CompetitionMasterClass whereMasterClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\CompetitionMasterClass whereOrderColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\CompetitionMasterClass whereThemeIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Competition\CompetitionMasterClass whereTitlesContent($value)
 * @mixin \Eloquent
 */
final class CompetitionMasterClass extends Pivot
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'competition_master_class';

    protected $casts = [
        'titles_content' => 'json',
        'theme_ids' => 'json',
    ];
}
