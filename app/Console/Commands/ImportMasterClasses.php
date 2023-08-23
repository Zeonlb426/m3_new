<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MasterClass\MasterClass;
use App\Models\Objects\VkLink;
use App\Models\Objects\YoutubeLink;
use App\Models\Sharing;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class ImportMasterClasses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:master-classes';

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
     *
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(): int
    {
        $baseURL = 'https://pokolenie.mts.ru/uploads/original';
        $tempLocation = sys_get_temp_dir() . '/';
        $arrayExt = ['jpg', 'jpeg', 'png', 'gif'];

        $this->newLine();
        $this->info('=== Начат процесс переноса Мастер-классов ===');

        MasterClass::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_video')->count();

        $this->line('Количество записей для переноса: ' . $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_video')
            ->select(
                'id',
                'sort',
                'name',
                'image',
                'link',
                'content',
                'group_id',
                'lead_id',
                'share_title',
                'share_description',
                'share_image',
                'enabled',
                'is_main',
                'created_at',
                'updated_at',
            )
            ->orderBy('id')
            ->chunk(5, function ($rows) use ($progressBar, $baseURL, $tempLocation, $arrayExt) {
                foreach ($rows as $row) {
                    $video = $this->parseVideo($row->id, $row->link);

                    $data = [
                        'id' => $row->id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'title' => $row->name,
                        'video_type' => $video['type'] ?? null,
                        'video_link' => $video['link'] ?? null,
                        'video_id' => $video['video_id'] ?? null,
                        'age_group_id' => $row->group_id,
                        'lead_id' => $row->lead_id,
                        'additional_signs' => \json_encode(['general' => $row->is_main]),
                        'visible_status' => $row->enabled,
                        'content' => $row->content,
                        'order_column' => $row->sort,
                    ];

                    \DB::table('master_classes')->insert($data);

                    $model = MasterClass::where('id', $row->id)->first();

                    if (!empty($row->image) && mb_substr($row->image, 0, 1) == '/') {

                        $url = $baseURL . $row->image;
                        $ext = pathinfo($url, PATHINFO_EXTENSION);

                        if (in_array($ext, $arrayExt)) {
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

                            if ($httpCode == 200 && is_string($image) && $fileSize > 0) {

                                $tempFile = $tempLocation . 'master_class_image_' . $row->id . '.' . $ext;

                                $fp = fopen($tempFile, "w");
                                fwrite($fp, $image);
                                fclose($fp);

                                $model->addMedia($tempFile)->toMediaCollection(MasterClass::IMAGE_COLLECTION);
                            };
                        };

                    }

                    $share = $model->sharing()->create([
                        'title' => $row->share_title,
                        'description' => $row->share_description,
                    ]);

                    if (!empty($row->share_image) && mb_substr($row->share_image, 0, 1) == '/') {
                        $url = $baseURL . $row->share_image;
                        $ext = pathinfo($url, PATHINFO_EXTENSION);

                        if (in_array($ext, $arrayExt)) {
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

                            if ($httpCode == 200 && is_string($image) && $fileSize > 0) {
                                $tempFile = $tempLocation . 'master_class_share_image_' . $row->id . '.' . $ext;

                                $fp = fopen($tempFile, "w");
                                fwrite($fp, $image);
                                fclose($fp);

                                $share->addMedia($tempFile)->toMediaCollection(Sharing::IMAGE_COLLECTION);
                            }
                        }
                    }
                }
                $progressBar->advance(5);
            })
        ;

        $maxID = \DB::table('master_classes')->max('id');
        $rawSelect = sprintf("SELECT setval('master_classes_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Мастер-классов закончен. ***');
        $this->newLine();

        $this->info('=== Начат процесс переноса зависимостей Мастер-классов с курсами ===');

        \DB::table('course_master_class')->truncate();

        $count = $dbOld->table('dvlp_course_video')->count();

        $this->line('Количество записей для переноса: ' . $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_course_video')
            ->select('id', 'course_id', 'video_id')
            ->orderBy('id')
            ->chunk(100, function ($rows) use ($progressBar) {
                $data = [];
                foreach ($rows as $row) {
                    $data[] = [
                        'id' => $row->id,
                        'course_id' => $row->course_id,
                        'master_class_id' => $row->video_id
                    ];
                }
                \DB::table('course_master_class')->insert($data);
                $progressBar->advance(100);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('course_master_class')->max('id');
        $rawSelect = sprintf("SELECT setval('course_master_class_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос зависимостей Мастер-классов с курсами закончен. ***');
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
