<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CompetitionWork\Work;
use App\Models\CompetitionWork\WorkVideo;
use App\Rules\VkLink;
use App\Rules\YoutubeLink;
use Illuminate\Console\Command;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportWorkImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:work-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring Work-images from the old DB';

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
        $arrayExt = ['jpg', 'jpeg', 'png'];
        $baseURL = 'https://pokolenie.mts.ru/uploads/original';
        $tempLocation = sys_get_temp_dir() . '/';

        $this->newLine();
        $this->info('=== Начат процесс переноса Картинок работ пользователей ===');

        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_work_images')->distinct('created_at')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $maxWorkID = \DB::table('works')->max('id');

        $rows = $dbOld->table('dvlp_work_images')
            ->distinct('created_at')
            ->get()
        ;

        foreach($rows as $row) {
            if($row->work_id > $maxWorkID) continue;
            if(empty($row->image) || mb_substr($row->image, 0, 1) !== '/') continue;

            $url = $baseURL . $row->image;
            $ext = pathinfo($url, PATHINFO_EXTENSION);
            if (!in_array($ext, $arrayExt)) continue;

            $work = Work::where('id', $row->work_id)->first();
            if($work === null) continue;

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

            if($httpCode === 200 && is_string($image) && $fileSize > 0) {

                $tempFile = $tempLocation . 'work_image_'. $row->id . '.' . $ext;

                $fp = fopen($tempFile, "w");
                fwrite($fp, $image);
                fclose($fp);

                $work->addMedia($tempFile)->toMediaCollection(Work::IMAGE_COLLECTION);

                if (!$work->hasMedia(Work::PREVIEW_COLLECTION)) {
                    $tempFilePreview = $tempLocation . 'work_prev_image_'. $row->id . '.' . $ext;

                    $fp = fopen($tempFilePreview, "w");
                    fwrite($fp, $image);
                    fclose($fp);

                    $size = getimagesize($tempFilePreview);

                    if ( $size[0] > 800) {
                        Image::load($tempFilePreview)
                            ->fit(Manipulations::FIT_CONTAIN, 800, 0)
                            ->save();
                    }

                    $work->addMedia($tempFilePreview)->toMediaCollection(Work::PREVIEW_COLLECTION);
                }
            }

            $progressBar->advance();
        }

        \DB::disconnect('pgsql_old');
        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Таблицы Картинок работ пользователей закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
