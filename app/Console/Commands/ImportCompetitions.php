<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition\Competition;
use App\Models\MasterClass\MasterClass;
use App\Models\Sharing;
use App\Rules\VkLink;
use App\Rules\YoutubeLink;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportCompetitions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:competitions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring competitions from the old DB';

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
    public function handle()
    {
        $baseURL = 'https://pokolenie.mts.ru/uploads/original';
        $tempLocation = sys_get_temp_dir() . '/';
        $arrayExt = ['jpg', 'jpeg', 'png'];

        $this->newLine();
        $this->info('=== Начат процесс переноса Конкурсов ===');

        Competition::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_competitions')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_competitions')
            ->select(
                'id',
                'sort',
                'name',
                'slug',
                'image',
                'content_short',
                'content_full',
                'partner_id',
                'partner_text',
                'share_title',
                'share_description',
                'size',
                'share_image',
                'enabled',
                'created_at',
                'updated_at',
                'preview_image',
                'end_text',
                'like_text'
            )
            ->orderBy('id')
            ->chunk(5, function ($rows) use ($progressBar, $baseURL, $tempLocation, $arrayExt) {
                foreach ($rows as $row) {
                    $data = [
                        'id' => $row->id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'title' => $row->name,
                        'slug' => $row->slug,
                        'period' => empty($row->end_text) ? null : $row->end_text,
                        'content' => empty($row->content_full) ? null : $row->content_full,
                        'short_content' => $row->content_short,
                        'titles_content' => \json_encode([
                            'like_text' => empty($row->like_text) ? '' : $row->like_text,
                            'section_name' => ['lead' => null, 'theme' => null, 'partner' => null, 'master-class' => null],
                            'add_work_text' => empty($row->download_alias) ? '' : $row->download_alias,
                            'works_enabled' => true,
                            'section_enabled' => ['lead' => true, 'theme' => false, 'partner' => true, 'master-class' => true],
                            'add_work_enabled' => true,
                            'works_filtration_enabled' => true,
                            'themes_enabled' => true,
                        ]),
                        'tile_size' => $row->size,
                        'visible_status' => $row->enabled,
                        'order_column' => $row->sort,
                    ];

                    \DB::table('competitions')->insert($data);

                    $model = Competition::where('id', $row->id)->first();

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
                            curl_setopt ($ch,CURLOPT_BINARYTRANSFER, true) ;
                            curl_setopt($ch, CURLOPT_HEADER, false);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            $image = curl_exec($ch);
                            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                            curl_close($ch);

                            if ($httpCode === 200 && is_string($image) && $fileSize > 0) {
                                $tempFile = $tempLocation . 'competitions_share_image_' . $row->id . '.' . $ext;

                                $fp = fopen($tempFile, "w");
                                fwrite($fp, $image);
                                fclose($fp);

                                $share->addMedia($tempFile)->toMediaCollection(Sharing::IMAGE_COLLECTION);
                            }
                        }
                    }

                    if (empty($row->image) || mb_substr($row->image, 0, 1) !== '/') continue;

                    $url = $baseURL . $row->image;

                    $ext = pathinfo($url, PATHINFO_EXTENSION);
                    if (!in_array($ext, $arrayExt)) continue;

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt ($ch,CURLOPT_BINARYTRANSFER, true) ;
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $image = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                    curl_close($ch);

                    if ($httpCode !== 200 || !is_string($image) || $fileSize == 0) continue;

                    $tempFile = $tempLocation . 'competitions_image_' . $row->id . '.' . $ext;

                    $fp = fopen($tempFile, "w");
                    fwrite($fp, $image);
                    fclose($fp);

                    $model->addMedia($tempFile)->toMediaCollection(Competition::COVER_COLLECTION);

                    if (empty($row->preview_image) || mb_substr($row->preview_image, 0, 1) !== '/') continue;

                    $url = $baseURL . $row->preview_image;
                    $ext = pathinfo($url, PATHINFO_EXTENSION);
                    if (!in_array($ext, $arrayExt)) continue;

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt ($ch,CURLOPT_BINARYTRANSFER, true) ;
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $image = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                    curl_close($ch);

                    if ($httpCode !== 200 || !is_string($image) || $fileSize == 0) continue;

                    $tempFile = $tempLocation . 'competitions_prev_image_' . $row->id . '.' . $ext;

                    $fp = fopen($tempFile, "w");
                    fwrite($fp, $image);
                    fclose($fp);

                    $model->addMedia($tempFile)->toMediaCollection(Competition::TILE_COLLECTION);
                }

                $progressBar->advance(5);
            })
        ;

        $maxID = \DB::table('competitions')->max('id');
        $rawSelect = sprintf("SELECT setval('competitions_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Конкурсов закончен. ***');
        $this->newLine();
        
        //--------------------------------------------------//

        $this->info('=== Начат процесс переноса Возрастных групп для конкурсов ===');

        \DB::table('competition_age_group')->truncate();

        $count = $dbOld->table('dvlp_competition_group')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_competition_group')
            ->select('id', 'competition_id', 'group_id', 'visible')
            ->orderBy('id')
            ->chunk(10, function ($rows) use ($progressBar) {
                $data = [];
                foreach($rows as $row) {
                    $data[] = [
                        'competition_id' => $row->competition_id,
                        'age_group_id' => $row->group_id,
                        'visible_status' => $row->visible
                    ];
                }
                \DB::table('competition_age_group')->insert($data);
                $progressBar->advance(10);
            })
        ;

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Возрастных групп конкурсов закончен. ***');
        $this->newLine();

        //--------------------------------------------------//

        $this->info('=== Начат процесс переноса Ведущих конкурсов ===');

        \DB::table('competition_lead')->truncate();

        $count = $dbOld->table('dvlp_competition_lead')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_competition_lead')
            ->select('id', 'competition_id', 'lead_id')
            ->orderBy('id')
            ->chunk(50, function ($rows) use ($progressBar) {
                $data = [];
                foreach($rows as $row) {
                    $data[] = [
                        'competition_id' => $row->competition_id,
                        'lead_id' => $row->lead_id,
                        'titles_content' => \json_encode([]),
                    ];
                }
                \DB::table('competition_lead')->insert($data);
                $progressBar->advance(50);
            })
        ;

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Ведущих конкурсов закончен. ***');
        $this->newLine();

        //--------------------------------------------------//

        $this->info('=== Начат процесс переноса Партнеров конкурсов ===');

        \DB::table('competition_partner')->truncate();

        $count = $dbOld->table('dvlp_competitions')->whereNotNull('partner_id')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_competitions')
            ->select('id', 'partner_id', 'partner_text')
            ->whereNotNull('partner_id')
            ->orderBy('id')
            ->chunk(5, function ($rows) use ($progressBar) {
                $data = [];
                foreach($rows as $row) {
                    $data[] = [
                        'competition_id' => $row->id,
                        'partner_id' => $row->partner_id,
                        'titles_content' => empty($row->partner_text) ? \json_encode([]) : \json_encode(['partner_text' => $row->partner_text]),
                    ];
                }
                \DB::table('competition_partner')->insert($data);
                $progressBar->advance(5);
            })
        ;

        $dbOld->table('dvlp_competition_partner')
            ->select('id', 'partner_id', 'competition_id', 'partner_text')
            ->orderBy('id')
            ->chunk(1, function ($rows) {
                $data = [];
                foreach($rows as $row) {
                    $data[] = [
                        'competition_id' => $row->competition_id,
                        'partner_id' => $row->partner_id,
                        'titles_content' => empty($row->partner_text) ? \json_encode([]) : \json_encode(['partner_text' => $row->partner_text]),
                    ];
                }
                \DB::table('competition_partner')->insert($data);
            })
        ;

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Партнеров конкурсов закончен. ***');
        $this->newLine();

        //--------------------------------------------------//

        $this->info('=== Начат процесс переноса Мастер-классов конкурсов ===');

        \DB::table('competition_master_class')->truncate();

        $count = $dbOld->table('dvlp_competition_video')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_competition_video')
            ->select('id', 'competition_id', 'video_id')
            ->orderBy('id')
            ->chunk(50, function ($rows) use ($progressBar) {
                $data = [];
                foreach($rows as $row) {
                    $data[] = [
                        'master_class_id' => $row->video_id,
                        'competition_id' => $row->competition_id,
                        'titles_content' => \json_encode([]),
                    ];
                }
                \DB::table('competition_master_class')->insert($data);
                $progressBar->advance(50);
            })
        ;

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Мастер-классов конкурсов закончен. ***');
        $this->newLine();

        //--------------------------------------------------//

        $this->info('=== Начат процесс переноса Категорий конкурсов ===');

        \DB::table('competition_theme')->truncate();

        $count = $dbOld->table('dvlp_competition_work_category')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_competition_work_category')
            ->select('id', 'competition_id', 'work_category_id')
            ->orderBy('id')
            ->chunk(10, function ($rows) use ($progressBar) {
                $data = [];
                foreach($rows as $row) {
                    $data[] = [
                        'theme_id' => $row->work_category_id,
                        'competition_id' => $row->competition_id,
                        'titles_content' => \json_encode([]),
                    ];
                }
                \DB::table('competition_theme')->insert($data);
                $progressBar->advance(10);
            })
        ;

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Категорий конкурсов закончен. ***');
        $this->newLine();

        //--------------------------------------------------//

        $this->info('=== Начат процесс переноса Типов работ конкурсов ===');

        \DB::table('competition_work_type')->truncate();

        $count = $dbOld->table('dvlp_competition_work_type')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_competition_work_type')
            ->select('id', 'competition_id', 'work_type_id')
            ->orderBy('id')
            ->chunk(20, function ($rows) use ($progressBar) {
                $data = [];
                foreach($rows as $row) {
                    $data[] = [
                        'competition_id' => $row->competition_id,
                        'work_type_id' => $row->work_type_id,
                    ];
                }
                \DB::table('competition_work_type')->insert($data);
                $progressBar->advance(20);
            })
        ;

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Типов работ конкурсов закончен. ***');
        $this->newLine();

        \DB::disconnect('pgsql_old');

        return CommandAlias::SUCCESS;
    }
}
