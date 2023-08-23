<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CompetitionWork\Work;
use App\Models\CompetitionWork\WorkVideo;
use App\Rules\VkLink;
use App\Rules\YoutubeLink;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportStatusWork extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:work-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check status Work';

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
        $this->info('=== Начат процесс определения статуса контента таблицы Works ===');

        $count = Work::count();

        $this->line('Количество записей: '. $count);
        $this->line('Идёт процесс определения статуса ...');

        $progressBar = $this->output->createProgressBar($count);

        $works = Work::all();

        foreach ($works as $work) {

            if (
                !$work->hasMedia(Work::IMAGE_COLLECTION) &&
                !$work->hasMedia(Work::AUDIO_COLLECTION) &&
                empty($work->work_video) &&
                empty($work->work_text)
            ) {
                $work->has_content = false;
                $work->save();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('*** Процесс определения статуса контента закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
