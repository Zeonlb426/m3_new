<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CompetitionWork\Work;
use App\Models\CompetitionWork\WorkVideo;
use App\Rules\VkLink;
use App\Rules\YoutubeLink;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportWorkText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:work-texts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring Work-texts from the old DB';

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
        $this->newLine();
        $this->info('=== Начат процесс переноса Таблицы Текстов ===');

        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_work_texts')->distinct('work_id')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $rows = $dbOld->table('dvlp_work_texts')
            ->distinct('work_id')
            ->get()
        ;
        foreach ($rows as $row) {

            if (empty($row->content)) continue;

            $work = Work::where('id', $row->work_id)->first();
            if ($work === null) continue;

            $work->work_text = trim(preg_replace('|\s+|',' ', $row->content));
            $work->save();
            $progressBar->advance();
        }

        \DB::disconnect('pgsql_old');
        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Таблицы Текстов закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
