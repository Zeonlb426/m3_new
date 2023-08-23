<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Location\Region;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportRegions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:regions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring regions from the old DB';

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
        $this->info('=== Начат процесс переноса Регионов ===');

        Region::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_regions')
            ->whereNotNull('code')
            ->count()
        ;
        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);


        $dbOld->table('dvlp_regions')
            ->whereNotNull('code')
            ->select('id', 'code','title')
            ->orderBy('id')
            ->chunk(20, function ($rows) use ($progressBar) {
                $data = [];
                foreach($rows as $row) {
                    $data[] = [
                        'id' => $row->id,
                        'title' => $row->title,
                        'code' => $row->code,
                    ];
                }
                \DB::table('regions')->insert($data);
                $progressBar->advance(20);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('regions')->max('id');
        $rawSelect = sprintf("SELECT setval('regions_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Регионов закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
