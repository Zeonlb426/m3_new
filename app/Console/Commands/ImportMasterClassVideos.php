<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MasterClass\MasterClass;
use App\Models\Objects\VkLink;
use App\Models\Objects\YoutubeLink;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class ImportMasterClassVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:master-classes:videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring master-classes from the old DB';

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
        $this->info('=== Начат процесс переноса Мастер-классов ===');

        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_video')->count();

        $this->line('Количество записей для переноса: ' . $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_video')
            ->select(
                'id',
                'link',
            )
            ->orderBy('id')
            ->chunk(5, function ($rows) use ($progressBar) {
                foreach ($rows as $row) {
                    $masterClass = MasterClass::whereId($row->id)->first(['id']);

                    if (null === $masterClass) {
                        $progressBar->advance();

                        continue;
                    }

                    $video = $this->parseVideo($row->id, $row->link);

                    MasterClass::whereId($row->id)->update([
                        'video_type' => $video['type'] ?? null,
                        'video_link' => $video['link'] ?? null,
                        'video_id' => $video['video_id'] ?? null,
                    ]);

                    $progressBar->advance();
                }
            })
        ;

        $progressBar->finish();

        \DB::disconnect('pgsql_old');

        $this->newLine();
        $this->info('*** Перенос Мастер-классов закончен. ***');
        $this->newLine();

        return self::SUCCESS;
    }


    /**
     * @param int $id
     * @param string|null $source
     *
     * @return array{type: \App\Enums\SocialVideoType::*, link: string, video_id: string}|null
     */
    private function parseVideo(int $id, ?string $source): ?array
    {
        if (Str::isEmpty($source)) {
            return null;
        }

        $video = YoutubeLink::tryParseUri($source);
        $video ??= VkLink::tryParseUri($source);

        if (null === $video) {
            $this->newLine()->error(\sprintf(
                'Unable to parse link for master-class with id "%d" and link "%s".', $id, $source,
            ));

            return null;
        }

        return [
            'type' => $video->getVideoType()->value,
            'link' => $video->__toString(),
            'video_id' => $video->getVideoId(),
        ];
    }
}
