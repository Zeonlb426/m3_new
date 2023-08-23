<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\User\ActionType;
use App\Models\CompetitionWork\Work;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportUserAddWork extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:user-add-work';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring Users credits for Add work from the old DB';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private readonly ActivityService $activityService)
    {
        parent::__construct();
    }

    /**
     * @return int
     */
    public function handle()
    {
        $this->newLine();
        $this->info('=== Начат процесс переноса Добавления работ Пользователей ===');

        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_user_transactions')
            ->where('name', '=', 'Добавление работы')
            ->count()
        ;

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_user_transactions')
            ->selectRaw("id, user_id, data::json->'work_id' as work_id")
            ->where('name', '=', 'Добавление работы')
            ->chunkById(1000, function ($rows) use ($progressBar) {
                foreach($rows as $row) {
                    $user = User::where('id', $row->user_id)->first();
                    if($user === null) continue;
                    $model = Work::where('id', $row->work_id)->first();
                    if($model === null) continue;

                    $this->activityService->addAction($user, $model, ActionType::ADD_WORK);
                }

                $progressBar->advance(1000);
            })
        ;

        \DB::disconnect('pgsql_old');

        $progressBar->finish();

        $this->newLine();
        $this->info('*** Перенос Добавления работ Пользователей закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
