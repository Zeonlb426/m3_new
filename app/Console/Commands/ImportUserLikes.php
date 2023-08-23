<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CompetitionWork\Work;
use App\Models\MasterClass\MasterClass;
use App\Models\News\News;
use App\Models\Promo\SuccessHistory;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportUserLikes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:user-likes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring Users likes from the old DB';


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
        $this->info('=== Начат процесс переноса Лайков Пользователей ===');

        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_like')->count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_like')
            ->select(
                'id',
                'user_id',
                'entity_hash',
                'entity_id',
                'created_at',
                'updated_at'
            )
            ->orderBy('id')
            ->chunk(100, function ($rows) use ($progressBar) {
                foreach($rows as $row) {
                    $user = User::where('id', $row->user_id)->first();
                    if($user === null) continue;
                    $model = null;
                    switch ($row->entity_hash) {
                        case 'e5e7cff8':
                            $model = Work::where('id', $row->entity_id)->first();
                            break;
                        case '17126fba':
                            $model = MasterClass::where('id', $row->entity_id)->first();
                            break;
                        case '9bc4ce68':
                            $model = SuccessHistory::where('id', $row->entity_id)->first();
                            break;
                        case '70230cda':
                            $model = News::where('id', $row->entity_id)->first();
                            break;
                    }
                    if($model === null) continue;

                    $this->activityService->addAction($user, $model);
                }

                $progressBar->advance(100);
            })
        ;

        \DB::disconnect('pgsql_old');

        $progressBar->finish();

        $this->newLine();
        $this->info('*** Перенос Лайков Пользователей закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
