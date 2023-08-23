<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

final class RepositoryMakeCommand extends GeneratorCommand
{
    /** @var string  */
    protected $name = 'make:repository';

    /** @var string  */
    protected $description = 'Create a new model repository';

    /** @var string  */
    protected $type = 'Repository';

    /**
     * @return bool|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle(): ?bool
    {
        if (false === parent::handle() && !$this->option('force')) {
            return false;
        }

        return null;
    }

    protected function getNameInput(): string
    {
        $name = \trim($this->argument('name'));
        if (!\str_contains($name, $this->type)) {
            $name .= $this->type;
        }

        return $name;
    }

    protected function getStub(): string
    {
        $stub = '/stubs/repository.stub';

        return \file_exists($customPath = $this->laravel->basePath(\trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Repositories';
    }

    protected function buildClass($name): string
    {
        $replace = [];

        if ($this->option('model')) {
            $replace = $this->buildModelReplacements($replace);
        }

        return \str_replace(
            \array_keys($replace), \array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace): array
    {
        $modelClass = $this->parseModel($this->option('model'));

        if (!$this->option('skip-model')
            && !\class_exists($modelClass)
            && $this->confirm("A {$modelClass} model does not exist. Do you want to generate it?", true)
        ) {
            $this->call('make:model', ['name' => $modelClass]);
        }

        return [
            '{{ namespacedModel }}' => $modelClass,
            '{{ model }}' => \class_basename($modelClass),
            '{{ modelVariable }}' => \lcfirst(\class_basename($modelClass)),
        ];
    }

    protected function parseModel($model): string
    {
        if (\preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new \InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }

    protected function getOptions(): array
    {
        return \array_merge([
            ['model', 'm', InputOption::VALUE_REQUIRED, 'Name of the model for which need generate repository'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the repository already exists'],
            ['skip-model', null, InputOption::VALUE_NONE, 'Skip model create.'],
        ], parent::getOptions());
    }
}
