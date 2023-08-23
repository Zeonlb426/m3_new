<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CompetitionWork\Work;
use App\Models\Objects\VkLink;
use App\Models\Objects\YoutubeLink;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ImportWorkVideo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:work-videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring Work-videos from the old DB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->newLine();
        $this->info('=== Начат процесс переноса Таблицы Работ Видео ===');

        $dbOld = DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_work_video')->distinct('work_id')->count();

        $this->line('Количество записей для переноса: ' . $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $maxWorkID = DB::table('works')->max('id');

        $dbOld->table('dvlp_work_video')
            ->distinct('work_id')
            ->orderBy('work_id')
            ->chunk(200, function (Collection $rows) use ($maxWorkID, $progressBar): void {
                foreach ($rows as $row) {
                    if ($row->work_id > $maxWorkID) {
                        $progressBar->advance();

                        continue;
                    }

                    $work = Work::whereId($row->work_id)->first(['id']);

                    if (null === $work) {
                        $progressBar->advance();

                        continue;
                    }

                    $videoLink = $this->normalizeVideoLink($row->video_link);

                    $video = YoutubeLink::tryParseUri($videoLink);
                    $video ??= VkLink::tryParseUri($videoLink);

                    if (null === $video) {
                        $this->newLine()->error(\sprintf(
                            'Unable to parse link for work with id "%d" and link "%s".', $work->id, $videoLink,
                        ));

                        $progressBar->advance();

                        continue;
                    }

                    Work::whereId($work->id)
                        ->update([
                            'work_video_type' => $video->getVideoType()->value,
                            'work_video' => $video->__toString(),
                            'work_video_id' => $video->getVideoId(),
                        ])
                    ;

                    $work->save();
                    $progressBar->advance();
                }
            })
        ;

        DB::disconnect('pgsql_old');

        $progressBar->finish();

        $this->newLine();
        $this->info('*** Перенос Таблицы Работ Видео закончен. ***');
        $this->newLine();

        return self::SUCCESS;
    }

    private function normalizeVideoLink(string $link): string
    {
        $link = \str_replace(["\n", "\r"], '', $link);

        $link = \ltrim($link, './');

        if (\preg_match('/http(?:s)?:\/(?!\/)/i', $link) > 0) {
            $link = \str_replace(['http:/', 'https:/'], ['http://', 'https://'], $link);
        }

        if (false === \str_contains($link, '://')) {
            $link = 'https://' . $link;
        }

        // replace cyrillic letter 'З' with number 3
        // case for one video in old database
        $link = Str::replace('З', '3', $link);

        return $link;
    }
}
