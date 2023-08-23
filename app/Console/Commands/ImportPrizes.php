<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition\Prize;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportPrizes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:prizes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring prizes from the old DB';

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
        $this->info('=== Начат процесс переноса Призов ===');

        Prize::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_prizes')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_prizes')
            ->select(
                'id',
                'competition_id',
                'name',
                'content',
                'image',
                'place',
                'link'
            )
            ->orderBy('id')
            ->chunk(5, function ($rows) use ($progressBar, $baseURL, $tempLocation, $arrayExt) {

                foreach ($rows as $row) {

                    $title = match ($row->place) {
                        1 => 'Приз за первое место',
                        2 => 'Приз за второе место',
                        3 => 'Приз за третье место',
                        default => 'Приз',
                    };
                    $data = [
                        'id' => $row->id,
                        'title' => $title,
                        'description' => empty($row->content) ? null : $row->content,
                        'link' => empty($row->link) ? null : $row->link,
                        'win_position' => $row->place,
                        'competition_id' => $row->competition_id,
                    ];

                    \DB::table('prizes')->insert($data);

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

                    $prize = Prize::where('id', $row->id)->first();

                    $tempFile = $tempLocation . 'prize_image_' . $row->id . '.' . $ext;

                    $fp = fopen($tempFile, "w");
                    fwrite($fp, $image);
                    fclose($fp);

                    $prize->addMedia($tempFile)->toMediaCollection(Prize::IMAGE_COLLECTION);
                }

                $progressBar->advance(5);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('prizes')->max('id');
        $rawSelect = sprintf("SELECT setval('prizes_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Призов закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
