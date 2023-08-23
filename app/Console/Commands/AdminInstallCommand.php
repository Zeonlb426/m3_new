<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
use Encore\Admin\Console\InstallCommand;

/**
 * Class AdminInstallCommand
 * @package App\Console\Commands
 */
final class AdminInstallCommand extends InstallCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:install {--login=} {--password=}';

    /**
     * Create tables and seed it.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function initDatabase(): void
    {
        $userModel = \config('admin.database.users_model');

        if ($userModel::count() === 0) {
            $this->truncate();
            $this->createPermissions();
            $this->createRoles();

            $this->createAdministrator(
                $this->option('login'),
                $this->option('password')
            );
        }
    }

    private function truncate(): void
    {
        Role::query()->truncate();
        Permission::query()->truncate();
        Administrator::query()->truncate();
    }

    /**
     * Create a permissions
     */
    private function createPermissions(): void
    {
        Permission::query()->insert([
            [
                'name' => 'All permission',
                'slug' => '*',
                'http_method' => '',
                'http_path' => '*',
            ],
            [
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'http_method' => 'GET',
                'http_path' => '/',
            ],
            [
                'name' => 'Login',
                'slug' => 'auth.login',
                'http_method' => '',
                'http_path' => "/auth/login\r\n/auth/logout",
            ],
            [
                'name' => 'User setting',
                'slug' => 'auth.setting',
                'http_method' => 'GET,PUT',
                'http_path' => '/auth/setting',
            ],
            [
                'name' => 'Auth management',
                'slug' => 'auth.management',
                'http_method' => '',
                'http_path' => "/auth/roles\r\n/auth/permissions\r\n/auth/menu\r\n/auth/logs",
            ],
        ]);
    }

    /**
     * Create a roles.
     */
    private function createRoles(): void
    {
        Role::query()->create([
            'name' => 'Administrator',
            'slug' => 'administrator',
        ]);

        Role::query()->create([
            'name' => 'Manager',
            'slug' => 'manager',
        ]);

        /** @var \Encore\Admin\Auth\Database\Role $role */
        $role = Role::query()->where('slug', '=', 'administrator')->first();
        $role->permissions()->save(Permission::query()->where('slug', '=', '*')->first());
    }

    /**
     * @param string $login
     * @param string $password
     */
    private function createAdministrator(string $login, string $password): void
    {
        $this->output->comment('Creating administrator');
        // create a user.
        Administrator::query()->create([
            'username' => $login,
            'password' => \bcrypt($password),
            'name' => 'Administrator',
        ]);

        /** @var \Encore\Admin\Auth\Database\Administrator $administrator */
        $administrator = Administrator::query()->where('username', '=', $login)->first();

        /** @var \Encore\Admin\Auth\Database\Role $role */
        $role = Role::query()->where('slug', '=', 'administrator')->first();

        $administrator->roles()->save($role);
    }
}
