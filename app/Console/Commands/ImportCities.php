<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Location\City;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportCities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:cities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring cities from the old DB';

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
        $this->info('=== Начат процесс переноса Городов ===');

        City::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_cities')
            ->whereNotNull('region_id')
            ->count()
        ;
        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_cities')
            ->whereNotNull('region_id')
            ->select('id', 'region_id', 'title')
            ->orderBy('id')
            ->chunk(100, function ($rows) use ($progressBar) {
                $data = [];
                foreach($rows as $row) {
                    $data[] = [
                        'id' => $row->id,
                        'title' => $row->title,
                        'region_id' => $row->region_id,
                    ];
                }
                \DB::table('cities')->insert($data);
                $progressBar->advance(100);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('cities')->max('id');
        $rawSelect = sprintf("SELECT setval('cities_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Городов закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
