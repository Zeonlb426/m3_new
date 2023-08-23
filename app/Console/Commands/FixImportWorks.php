<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CompetitionWork\Work;
use App\Models\CompetitionWork\WorkAuthor;
use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class FixImportWorks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:works-fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring Works from the old DB';

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
        $this->info('=== Начат процесс исправления статуса работ ===');

        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_works')->count();
        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_works')
            ->select('id', 'status_key')
            ->orderBy('id')
            ->chunk(1000, function ($rows) use ($progressBar, $dbOld) {
                foreach ($rows as $row) {

                    $work = Work::where('id', $row->id)->first();

                    if ($work === null) continue;

                    $work->status = $row->status_key;
                    $work->save(['timestamps' => false]);
                }
                $progressBar->advance(1000);
            })
        ;

        \DB::disconnect('pgsql_old');

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Процесс исправления статуса работ закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
