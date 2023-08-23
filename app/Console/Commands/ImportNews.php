<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\News\News;
use App\Models\Objects\VkLink;
use App\Models\Objects\YoutubeLink;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class ImportNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring news from the old DB';

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
     *
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(): int
    {
        $baseURL = 'https://pokolenie.mts.ru/uploads/original';
        $tempLocation = sys_get_temp_dir() . '/';
        $arrayExt = ['jpg', 'jpeg', 'png'];

        $this->newLine();
        $this->info('=== Начат процесс переноса Новостей ===');

        News::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_news')->count();

        $this->line('Количество записей для переноса: ' . $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_news')
            ->select(
                'id',
                'title',
                'video_link',
                'thumb',
                'preview',
                'announce',
                'description',
                'date',
                'created_at',
                'updated_at',
                'slug'
            )
            ->orderBy('id')
            ->chunk(5, function ($rows) use ($progressBar, $baseURL, $tempLocation, $arrayExt) {
                foreach ($rows as $row) {
                    $video = $this->parseVideo($row->id, $row->video_link);

                    $data = [
                        'id' => $row->id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'title' => $row->title,
                        'slug' => $row->slug,
                        'announce' => $row->announce,
                        'video_type' => $video['type'] ?? null,
                        'video_link' => $video['link'] ?? null,
                        'video_id' => $video['video_id'] ?? null,
                        'visible_status' => true,
                        'publish_date' => $row->date ?? $row->created_at,
                        'content' => $row->description,
                    ];

                    \DB::table('news')->insert($data);

                    if (empty($row->thumb) || mb_substr($row->preview, 0, 1) !== '/') continue;

                    $url = $baseURL . $row->preview;

                    $ext = pathinfo($url, PATHINFO_EXTENSION);
                    if (!in_array($ext, $arrayExt)) continue;

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $image = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                    curl_close($ch);

                    if ($httpCode !== 200 || !is_string($image) || $fileSize == 0) continue;

                    $news = News::where('id', $row->id)->first();

                    $tempFile = $tempLocation . 'news_image_' . $row->id . '.' . $ext;

                    $fp = fopen($tempFile, "w");
                    fwrite($fp, $image);
                    fclose($fp);

                    $news->addMedia($tempFile)->toMediaCollection(News::COVER_COLLECTION);
                }

                $progressBar->advance(5);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('news')->max('id');
        $rawSelect = sprintf("SELECT setval('news_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Новостей закончен. ***');
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

        $source = \trim($source);

        if (\preg_match('/^[a-z0-9-_]{11}$/i', $source) > 0) {
            $source = 'https://youtube.com/watch?v=' . $source;
        }

        $video = YoutubeLink::tryParseUri($source);
        $video ??= VkLink::tryParseUri($source);

        if (null === $video) {
            $this->newLine()->error(\sprintf(
                'Unable to parse link for news with id "%d" and link "%s".', $id, $source,
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
