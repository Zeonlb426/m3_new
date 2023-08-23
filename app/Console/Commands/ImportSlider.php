<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Slider;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportSlider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:sliders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring sliders from the old DB';

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
        $this->info('=== Начат процесс переноса Слайдов ===');

        Slider::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_sliders')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_sliders')
            ->select(
                'id',
                'name',
                'image_path',
                'enable',
                'sort',
                'description',
                'subtitle',
                'mobile_image_path',
                'url'
            )
            ->orderBy('id')
            ->chunk(5, function ($rows) use ($progressBar, $baseURL, $tempLocation, $arrayExt) {

                foreach($rows as $row) {
                    $data = [
                        'id' => $row->id,
                        'short_title' => $row->subtitle,
                        'title' => $row->name,
                        'link' => $row->url,
                        'description' => $row->description,
                        'order_column' => $row->sort,
                        'visible_status' => $row->enable,
                    ];

                    \DB::table('sliders')->insert($data);

                    if (empty($row->image_path) || mb_substr($row->image_path, 0, 1) !== '/') continue;

                    $url = $baseURL . $row->image_path;

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

                    $slide = Slider::where('id', $row->id)->first();

                    $tempFile = $tempLocation . 'slider_image_' . $row->id . '.' . $ext;

                    $fp = fopen($tempFile, "w");
                    fwrite($fp, $image);
                    fclose($fp);

                    $slide->addMedia($tempFile)->toMediaCollection(Slider::IMAGE_COLLECTION);

                    if (empty($row->mobile_image_path) || mb_substr($row->mobile_image_path, 0, 1) !== '/') continue;

                    $url = $baseURL . $row->mobile_image_path;

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

                    $tempFile = $tempLocation . 'slider_mobile_image_' . $row->id . '.' . $ext;

                    $fp = fopen($tempFile, "w");
                    fwrite($fp, $image);
                    fclose($fp);

                    $slide->addMedia($tempFile)->toMediaCollection(Slider::IMAGE_MOBILE_COLLECTION);
                }

                $progressBar->advance(5);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('sliders')->max('id');
        $rawSelect = sprintf("SELECT setval('sliders_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Слайдов закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
