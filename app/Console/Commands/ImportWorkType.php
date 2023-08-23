<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition\WorkType;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportWorkType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:work-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring work-type from the old DB';

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
        $this->info('=== Начат процесс переноса Типов работ ===');

        WorkType::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_work_type')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_work_type')
            ->select('id', 'name', 'format', 'status_key')
            ->orderBy('id')
            ->chunk(4, function ($rows) use ($progressBar) {
                $data = [];
                foreach($rows as $row) {
                    $data[] = [
                        'id' => $row->id,
                        'title' => $row->name,
                        'formats' => $row->format !== null ? \json_encode(explode(',', $row->format)) : null,
                        'visible_status' => $row->status_key === 1,
                    ];
                }
                \DB::table('work_types')->insert($data);
                $progressBar->advance(4);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('work_types')->max('id');
        $rawSelect = sprintf("SELECT setval('work_types_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Типов работ закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
