<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AgeGroup;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportAgeGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:age-groups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring age groups from the old DB';

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
        $this->info('=== Начат процесс переноса Возрастных групп ===');

        AgeGroup::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_groups')->count()
        ;
        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_groups')
            ->select('id', 'name', 'slug', 'sort', 'enabled', 'min', 'max')
            ->orderBy('id')
            ->chunk(2, function ($rows) use ($progressBar) {
                foreach($rows as $row) {
                    $data = [
                        'id' => $row->id,
                        'title' => $row->name,
                        'slug' => $row->slug,
                        'min_age' => $row->min,
                        'max_age' => $row->max,
                    ];
                    \DB::table('age_groups')->insert($data);
                }
                $progressBar->advance(2);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('age_groups')->max('id');
        $rawSelect = sprintf("SELECT setval('age_groups_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Возрастных групп закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
