<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition\Theme;
use App\Models\Slider;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportWorkCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:work-category';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring work-category from the old DB';

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
        $this->info('=== Начат процесс переноса Категорий работ ===');

        Theme::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_work_category')->count();

        $this->line('Количество записей для переноса: ' . $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_work_category')
            ->select(
                'id',
                'title',
                'full_description',
                'image',
                'size',
                'work_preview_image',
            )
            ->orderBy('id')
            ->chunk(3, function ($rows) use ($progressBar, $baseURL, $tempLocation, $arrayExt) {

                foreach ($rows as $row) {
                    $data = [
                        'id' => $row->id,
                        'title' => $row->title,
                        'description' => $row->full_description,
                        'tile_size' => $row->size,
                    ];

                    \DB::table('themes')->insert($data);

                    $theme = Theme::where('id', $row->id)->first();

                    $url = $baseURL . $row->image;

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

                        if ($httpCode == 200 && is_string($image) && $fileSize > 0) {
                            $tempFile = $tempLocation . 'theme_image_' . $row->id . '.' . $ext;

                            $fp = fopen($tempFile, "w");
                            fwrite($fp, $image);
                            fclose($fp);

                            $theme->addMedia($tempFile)->toMediaCollection(Theme::TILE_COLLECTION);
                        }
                    }

                    $url = $baseURL . $row->work_preview_image;
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

                        if ($httpCode == 200 && is_string($image) && $fileSize > 0) {
                            $tempFile = $tempLocation . 'theme_cover_image_' . $row->id . '.' . $ext;

                            $fp = fopen($tempFile, "w");
                            fwrite($fp, $image);
                            fclose($fp);

                            $theme->addMedia($tempFile)->toMediaCollection(Theme::COVER_COLLECTION);
                        }
                    }
                }

                $progressBar->advance(3);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('themes')->max('id');
        $rawSelect = sprintf("SELECT setval('themes_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Категорий работ закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
