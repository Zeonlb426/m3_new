<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CompetitionWork\Work;
use App\Models\CompetitionWork\WorkAuthor;
use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportWorks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:works';

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
        $this->info('=== Начат процесс переноса Таблицы Работ ===');

        Work::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_works')->count();
        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $maxUserID = \DB::table('users')->max('id');

//        $arrayAuthors =  $dbOld->table('dvlp_work_authors')
//            ->distinct('work_id')
//            ->get()
//            ->keyBy('work_id')
//        ;

        $dbOld->table('dvlp_works')
            ->select(
                'id',
                'user_id',
                'competition_id',
                'work_type_id',
                'enabled',
                'count_like',
                'created_at',
                'updated_at',
                'work_category_id',
                'status_key',
                'sort'

            )
            ->orderBy('id')
            ->chunk(100, function ($rows) use ($progressBar, $maxUserID, $dbOld) {
                $data = [];
                foreach ($rows as $row) {

                    if ($row->user_id > $maxUserID) continue;

                    $workAuthorOld = $dbOld->table('dvlp_work_authors')
                        ->select('full_name', 'birthday', 'created_at')
                        ->where('work_id', $row->id)
                        ->first()
                    ;
                    if ($workAuthorOld != null) {
                        $name = mb_substr(
                            trim(
                                preg_replace(
                                    '|\s+|',
                                    ' ',
                                    $workAuthorOld->full_name
                                    //mb_convert_case(strtolower($workAuthorOld->full_name), MB_CASE_TITLE, 'UTF-8')
                                )
                            ),
                            0,
                            250
                        );
                        $birthDate = is_null($workAuthorOld->birthday) ? '2010-01-01' : $workAuthorOld->birthday;
                        $createdAt = $workAuthorOld->created_at;
                    }else{
                        $user = User::where('id', $row->user_id)->first();
                        $name = $user->first_name.' '.$user->last_name;
                        $birthDate = is_null($user->birth_date) ? '2010-01-01' : $user->birth_date;
                        $createdAt = $user->created_at;
                    }

                    $workAuthor = WorkAuthor::firstOrCreate(
                        [
                            'user_id' => $row->user_id,
                            'name' => $name
                        ],
                        [
                            'birth_date' => $birthDate,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]
                    );

                    $data[] = [
                        'id' => $row->id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'status' => $row->status_key,
                        'order_column' => $row->sort,
                        'likes_total_count' => $row->count_like,
                        'user_id' => $row->user_id,
                        'author_id' => $workAuthor->id,
                        'competition_id' => $row->competition_id,
                        'theme_id' => $row->work_category_id,
                        'work_type_id' => $row->work_type_id,
                    ];
                }
                \DB::table('works')->insert($data);
                $progressBar->advance(100);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('works')->max('id');
        $rawSelect = sprintf("SELECT setval('works_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Перенос Таблицы Работ закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
