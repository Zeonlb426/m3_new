<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CompetitionWork\Work;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportWorkAudios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:work-audios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring Audio works from the old DB';

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
     * @return int
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig|\Spatie\Image\Exceptions\InvalidManipulation
     */
    public function handle(): int
    {
        $baseURL = 'https://pokolenie.mts.ru/uploads/original';
        $tempLocation = sys_get_temp_dir() . '/';
        $arrayExt = ['mp3', 'ogg', 'acc', 'wav'];

        $this->newLine();
        $this->info('=== Начат процесс переноса Аудио файлов пользователей ===');

        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_work_audios')
            ->where('audio', 'LIKE', '/%.mp3')
            ->orWhere('audio', 'LIKE', '/%.ogg')
            ->orWhere('audio', 'LIKE', '/%.acc')
            ->orWhere('audio', 'LIKE', '/%.wav')
            ->distinct('work_id')
            ->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $maxWorkID = \DB::table('works')->max('id');

        $progressBar = $this->output->createProgressBar($count);

        $rows = $dbOld->table('dvlp_work_audios')
            ->where('audio', 'LIKE', '/%.mp3')
            ->orWhere('audio', 'LIKE', '/%.ogg')
            ->orWhere('audio', 'LIKE', '/%.acc')
            ->orWhere('audio', 'LIKE', '/%.wav')
            ->distinct('work_id')
            ->get();

        foreach($rows as $row) {

            if($row->work_id > $maxWorkID) continue;

            $url = $baseURL . $row->audio;

            $ext = pathinfo($url, PATHINFO_EXTENSION);
            if (empty($ext) || !in_array($ext, $arrayExt)) continue;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt ($ch,CURLOPT_BINARYTRANSFER, true) ;
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $audio = curl_exec($ch);
            curl_close($ch);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

            if($httpCode !== 200 || $fileSize === 0) continue;

            $work = Work::where('id', $row->work_id)->first();

            if($work === null) continue;

            $tempFile = $tempLocation . 'audio_' . $row->id . '.' . $ext;

            $fp = fopen($tempFile, "w");
            fwrite($fp, $audio);
            fclose($fp);

            $work->addMedia($tempFile)->toMediaCollection(Work::AUDIO_COLLECTION);

            $progressBar->advance();
        }

        \DB::disconnect('pgsql_old');

        $progressBar->finish();

        $this->newLine();
        $this->info('*** Перенос Аудио файлов пользователей закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
