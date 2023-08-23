<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Encore\Admin\Auth\Database\Permission;

/**
 * Class CreateUserCommand
 * @package App\Console\Commands
 */
final class CreateUserCommand extends \Encore\Admin\Console\CreateUserCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-user {--login=} {--password=} {--role=} {--name=}';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $userModel = \config('admin.database.users_model');
        $roleModel = \config('admin.database.roles_model');

        [$loginOption, $passwordOption, $nameOption, $roleOption] = $this->getCommandOptions();

        $roleSelected = $roleOption;

        /** @var \Encore\Admin\Auth\Database\Role $role */
        $roles = $roleModel::query()->get();

        if (empty($roleSelected)) {
            /** @var array $roleSelected */
            $selectedOptions = $roles->pluck('slug')->toArray();
            $roleSelected = $this->choice('Please choose a role for the user', $selectedOptions, null, null, true);
        }

        $roles = $roles->filter(static function ($role) use ($roleSelected) {
            return \in_array($role->slug, $roleSelected, true);
        });

        $user = new $userModel([
            'username' => $loginOption,
            'password' => $passwordOption,
            'name' => $nameOption,
        ]);

        try {
            $user->save();
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if (!empty($roles)) {
            $user->roles()->attach($roles);

            foreach ($roles as $role) {
                $role->permissions()->save(Permission::query()->where('slug', '=', 'dashboard')->first());
            }
        }


        $this->info("User [$nameOption] created successfully.");

        return self::SUCCESS;
    }

    /**
     * @return array
     */
    private function getCommandOptions(): array
    {
        do {
            $loginOption = $this->option('login') ?? $this->ask('Please enter a username to login');
        } while ($loginOption === null);

        do {
            $passwordOption = $this->option('password') ?? $this->secret('Please enter a password to login');
        } while ($passwordOption === null);

        $passwordOption = \bcrypt($passwordOption);

        do {
            $nameOption = $this->option('name') ?? $this->ask('Please enter a name to display');
        } while ($nameOption === null);

        $roleOption = $this->option('role') === null ? [] : \explode(',', $this->option('role'));

        return [$loginOption, $passwordOption, $nameOption, $roleOption];
    }
}
