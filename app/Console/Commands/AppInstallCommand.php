<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Class AppInstallCommand
 * @package App\Console\Commands
 */
final class AppInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install
                            {--login=admin:administrator,manager:manager : Admin login}
                            {--password= : Admin password}
                            {--key-generate= : Whether to generate new application key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install application';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $tableRows = [];

        $loginOption = $this->option('login');
        $passwordOption = $this->option('password');
        $generateKey = $this->option('key-generate'); //by default APP_KEY value is in vault

        $inputUsers = \explode(',', $loginOption);
        $inputPasswords = $passwordOption === null ? [] : \explode(',', $passwordOption);

        foreach ($inputUsers as $key => $user) {
            [$user, $role] = \explode(':', $user);

            if (false === \array_key_exists($key, $inputPasswords)) {
                $inputPasswords[$key] = $this->ask("Password for \"{$user}\":", Str::random(8));
            }

            $tableRows[$user] = [
                'login' => $user,
                'role' => $role,
                'password' => $inputPasswords[$key],
            ];
        }

        if ($generateKey) {
            $this->comment('Generating app key...');
            $this->callSilent('key:generate', ['--force']);
        }

        $this->comment('Installing admin...');

        foreach ($tableRows as $username => $tableRow) {
            $this->info("Adding {$username} user...");
            if ($username === 'admin') {
                $this->callSilent('admin:install', [
                    '--login' => $tableRow['login'],
                    '--password' => $tableRow['password'],
                ]);
            } else {
                $this->callSilent('admin:create-user', [
                    '--login' => $tableRow['login'],
                    '--password' => $tableRow['password'],
                    '--role' => $tableRow['role'],
                    '--name' => \ucfirst($tableRow['login']),
                ]);
            }
        }
        $this->output->success('Admin install complete');

        $this->comment('Importing menu...');
        $this->callSilent('admin:import-menu-config');
        $this->output->success('Menu import complete');

        $this->table(['Login', 'Display name', 'Password'], $tableRows);

        return self::SUCCESS;
    }
}
