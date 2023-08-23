<?php

declare(strict_types=1);

namespace App\Console\Commands;

/**
 * Class SyncCommand
 * @package App\Console\Commands
 */
final class SyncCommand extends \Illuminate\Console\Command
{
    protected $signature = 'env:clear {--path= : Gets the path to scan for files}';

    protected $description = 'Clearing environment settings';

    /**
     * @return int
     */
    public function handle(): int
    {
        $this->info('Clearing...');

        $filename = $this->getFilename();
        $this->clear($filename);

        $this->info("The found keys were successfully saved to the {$filename} file.");

        return self::SUCCESS;
    }

    /**
     * @param string $filename
     *
     * @return void
     */
    protected function clear(string $filename): void
    {
        $forces = \config('env-sync.forces');

        $newLines = [];
        $lines = \file($filename);

        foreach ($lines as $line) {
            $countNewLine = \count($newLines);
            $indexLastNewLine = $countNewLine === 0 ? 0 : $countNewLine - 1;

            if ($line === "\n") {
                if ($indexLastNewLine > 0 && $newLines[$indexLastNewLine] !== "\n") {
                    $newLines[] = $line;
                }

                continue;
            }

            [$key, $value] = \explode('=', $line);

            if (\in_array($key, $this->excludeEnv(), true)) {
                continue;
            }

            if (false !== \array_key_exists($key, $forces)) {
                $newLines[] = $line;

                continue;
            }

            $clearedValue = \preg_replace('[\n|\"+]', '', $value);

            if ($clearedValue === 'null' || $clearedValue === '') {
                $newLines[] = "$key=null\n";
            }
        }

        \file_put_contents($filename, $newLines);
    }

    /**
     * @return string
     */
    protected function path(): string
    {
        return $this->getOptionPath() ?: $this->getRealPath();
    }

    /**
     * @return string
     */
    protected function getFilename(): string
    {
        return '.env.example';
    }

    /**
     * @return string|null
     */
    protected function getOptionPath(): ?string
    {
        return $this->option('path');
    }

    /**
     * @return string
     */
    protected function getRealPath(): string
    {
        return \realpath(\base_path());
    }

    /**
     * @return array
     */
    protected function excludeEnv(): array
    {
        return [
            'SESSION_CONNECTION',
            'SESSION_DOMAIN',
            'SESSION_DRIVER',
            'SESSION_LIFETIME',
            'SESSION_SECURE_COOKIE',
            'SESSION_STORE',
        ];
    }
}
