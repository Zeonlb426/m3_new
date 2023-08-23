<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\User\ActionType;
use App\Models\CompetitionWork\Work;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportUserRegistration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:user-registration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring Users registration credits from the old DB';


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
        $this->info('=== Начат процесс переноса Кредитов за регистрацию Пользователей ===');

        $count = User::count();

        $this->line('Количество записей для переноса: '. $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        User::chunkById(1000, function ($rows) use ($progressBar) {
            foreach($rows as $row) {
                $user = User::where('id', $row->id)->first();
                $this->activityService->addAction($user, $user, ActionType::REGISTRATION);
            }
            $progressBar->advance(1000);
        });

        $progressBar->finish();

        $this->newLine();
        $this->info('*** Перенос Кредитов за регистрацию Пользователей закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
