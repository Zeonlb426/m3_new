<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition\Partner;
use App\Models\MasterClass\MasterClass;
use App\Models\Sharing;
use App\Rules\VkLink;
use App\Rules\YoutubeLink;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportPartners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:partners';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring partners from the old DB';

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
        $this->info('=== Начат процесс переноса Партнеров ===');

        Partner::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_partners')->count();

        $this->line('Количество записей для переноса: ' . $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_partners')
            ->select(
                'id',
                'name',
                'image',
                'url',
                'enabled',
                'content_full',
                'background',
            )
            ->orderBy('id')
            ->chunk(5, function ($rows) use ($progressBar, $baseURL, $tempLocation, $arrayExt) {
                foreach($rows as $row) {
                    $data = [
                        'id' => $row->id,
                        'title' => $row->name,
                        'link' => $row->url,
                        'visible_status' => $row->enabled,
                        'description' => empty($row->content_full) ? '<p>Партнер</p>' : $row->content_full,
                    ];

                    \DB::table('partners')->insert($data);

                    if(empty($row->image) || mb_substr($row->image, 0, 1) !== '/') continue;

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

                    $model = Partner::where('id', $row->id)->first();

                    $tempFile = $tempLocation . 'partner_image_' . $row->id . '.' . $ext;
                    $tempFileSlider = $tempLocation . 'partner_slider_image_'. $row->id . '.' . $ext;

                    $fp = fopen($tempFile, "w");
                    fwrite($fp, $image);
                    fclose($fp);

                    $fp_slide = fopen($tempFileSlider, "w");
                    fwrite($fp_slide, $image);
                    fclose($fp_slide);

                    $model->addMedia($tempFile)->toMediaCollection(Partner::LOGO_COLLECTION);
                    $model->addMedia($tempFileSlider)->toMediaCollection(Partner::SLIDER_COLLECTION);

                    if(empty($row->background) || mb_substr($row->background, 0, 1) !== '/') continue;

                    $url = $baseURL . $row->background;

                    $ext = pathinfo($url, PATHINFO_EXTENSION);
                    if (!in_array($ext, $arrayExt)) continue;

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt ($ch,CURLOPT_BINARYTRANSFER, true) ;
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $background = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                    curl_close($ch);

                    if ($httpCode !== 200 || !is_string($image) || $fileSize == 0) continue;

                    $tempFile = $tempLocation . 'partner_bg_image_' . $row->id . '.' . $ext;

                    $fp = fopen($tempFile, "w");
                    fwrite($fp, $background);
                    fclose($fp);

                    $model->addMedia($tempFile)->toMediaCollection(Partner::BACKGROUND_COLLECTION);
                }
                $progressBar->advance(5);
            })
        ;

        $maxID = \DB::table('partners')->max('id');
        $rawSelect = sprintf("SELECT setval('partners_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Партнеров закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
