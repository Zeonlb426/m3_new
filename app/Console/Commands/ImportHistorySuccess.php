<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Objects\VkLink;
use App\Models\Objects\YoutubeLink;
use App\Models\Promo\SuccessHistory;
use App\Models\Sharing;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class ImportHistorySuccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:history-success';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring history success from the old DB';

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
        $this->info('=== Начат процесс переноса Историй Успеха ===');

        SuccessHistory::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_history_success')->count();

        $this->line('Количество записей для переноса: ' . $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_history_success')
            ->select(
                'id',
                'sort',
                'title',
                'title_short',
                'content_short',
                'content_full',
                'image',
                'share_title',
                'share_description',
                'share_image',
                'enabled',
                'created_at',
                'updated_at',
                'video_link',
            )
            ->orderBy('id')
            ->chunk(3, function ($rows) use ($progressBar, $baseURL, $tempLocation, $arrayExt) {

                foreach ($rows as $row) {
                    $video = $this->parseVideo($row->id, $row->video_link);

                    $data = [
                        'id' => $row->id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'short_title' => $row->title_short,
                        'title' => $row->title,
                        'video_type' => $video['type'] ?? null,
                        'video_link' => $video['link'] ?? null,
                        'video_id' => $video['video_id'] ?? null,
                        'visible_status' => $row->enabled,
                        'short_description' => $row->content_short,
                        'description' => $row->content_full,
                        'order_column' => $row->sort,
                    ];

                    \DB::table('success_histories')->insert($data);

                    if (empty($row->image) || mb_substr($row->image, 0, 1) !== '/') continue;

                    $url = $baseURL . $row->image;

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

                    $tempFile = $tempLocation . 'history_image_' . $row->id . '.' . $ext;
                    $shModel = SuccessHistory::where('id', $row->id)->first();

                    $fp = fopen($tempFile, "w");
                    fwrite($fp, $image);
                    fclose($fp);

                    $shModel->addMedia($tempFile)->toMediaCollection(SuccessHistory::IMAGE_COLLECTION);

                    $share = $shModel->sharing()->create([
                        'title' => $row->share_title,
                        'description' => $row->share_description,
                    ]);

                    if (empty($row->share_image) || mb_substr($row->share_image, 0, 1) !== '/') continue;

                    $url = $baseURL . $row->share_image;

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

                    $tempFile = $tempLocation . 'history_share_image_' . $row->id . '.' . $ext;

                    $fp = fopen($tempFile, "w");
                    fwrite($fp, $image);
                    fclose($fp);

                    $share->addMedia($tempFile)->toMediaCollection(Sharing::IMAGE_COLLECTION);
                }

                $progressBar->advance(3);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('success_histories')->max('id');
        $rawSelect = sprintf("SELECT setval('success_histories_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Историй Успеха закончен. ***');
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
                'Unable to parse link for history success with id "%d" and link "%s".', $id, $source,
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
