<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Competition\WorkType;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportWorkCategoryLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:work-category-leads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring work-category-leads from the old DB';

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
        $this->info('=== Начат процесс переноса связи Ведущих с категориями работ ===');

        \DB::table('lead_theme')->truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_work_category_lead')->count();

        $this->line('Количество записей для переноса: ' . $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_work_category_lead')
            ->select('id', 'work_category_id', 'lead_id')
            ->orderBy('id')
            ->chunk(10, function ($rows) use ($progressBar) {
                $data = [];
                foreach ($rows as $row) {
                    $data[] = [
                        'theme_id' => $row->work_category_id,
                        'lead_id' => $row->lead_id
                    ];
                }
                \DB::table('lead_theme')->insert($data);
                $progressBar->advance(10);
            })
        ;

        \DB::disconnect('pgsql_old');

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос связи Ведущих с категориями работ закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
