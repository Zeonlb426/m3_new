<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MasterClass\Course;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:courses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring courses from the old DB';

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
        $this->info('=== Начат процесс переноса Курсов мастер-классов ===');

        Course::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_course')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_course')
            ->select('id', 'name', 'slug', 'content', 'enabled', 'created_at', 'updated_at')
            ->orderBy('id')
            ->chunk(5, function ($rows) use ($progressBar) {
                $data = [];
                foreach($rows as $row) {
                    $data[] = [
                        'id' => $row->id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'name' => $row->name,
                        'slug' => $row->slug,
                        'visible_status' => $row->enabled,
                        'description' => $row->content,
                    ];
                }
                \DB::table('courses')->insert($data);
                $progressBar->advance(5);
            })
        ;

        $maxID = \DB::table('courses')->max('id');
        $rawSelect = sprintf("SELECT setval('courses_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Курсов закончен. ***');
        $this->newLine();

        $this->info('=== Начат процесс переноса зависимостей курсов с ведущими ===');

        \DB::table('course_lead')->truncate();

        $count = $dbOld->table('dvlp_course_lead')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_course_lead')
            ->select('id', 'course_id', 'lead_id')
            ->orderBy('id')
            ->chunk(5, function ($rows) use ($progressBar) {
                $data = [];
                foreach($rows as $row) {
                    $data[] = [
                        'id' => $row->id,
                        'course_id' => $row->course_id,
                        'lead_id' => $row->lead_id
                    ];
                }
                \DB::table('course_lead')->insert($data);
                $progressBar->advance(5);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('course_lead')->max('id');
        $rawSelect = sprintf("SELECT setval('course_lead_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос зависимостей курсов с ведущими закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
