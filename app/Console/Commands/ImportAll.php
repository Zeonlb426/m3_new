<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Launch all imports';

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
//        $res = $this->call('import:regions');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:cities');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:news');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:leads');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:history-success');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:age-groups');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:courses');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:master-classes');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:partners');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:sliders');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:work-type');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:work-category');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:work-category-leads');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:users');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
        $res = $this->call('import:avatars');
        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:competitions');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:prizes');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:works');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:work-videos');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:work-texts');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:work-audios');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:work-images');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:work-status');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:user-registration');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:user-likes');
//        if ($res !== 0) return CommandAlias::FAILURE;
//
//        $res = $this->call('import:user-add-work');
//        if ($res !== 0) return CommandAlias::FAILURE;

        return CommandAlias::SUCCESS;
    }
}
