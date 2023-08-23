<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand as BaseModelMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

final class ModelMakeCommand extends BaseModelMakeCommand
{
    public function handle(): ?bool
    {
        if (false === parent::handle() && !$this->option('force')) {
            return false;
        }

        if ($this->option('repository')) {
            $this->repositoryGenerate();
        }

        return null;
    }

    private function repositoryGenerate(): void
    {
        $repository = Str::studly($this->argument('name'));

        $params = [
            'name' => $repository,
            '--model' => $this->qualifyClass($this->getNameInput()),
            '--skip-model' => 1,
        ];

        if ($this->option('force')) {
            $params['--force'] = 1;
        }

        $this->call('make:repository', $params);
    }

    protected function getOptions(): array
    {
        return \array_merge([
            ['repository', null, InputOption::VALUE_NONE, 'Generate a new repository for model'],
        ], parent::getOptions());
    }
}
