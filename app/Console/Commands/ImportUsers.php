<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Rules\User\Email;
use App\Rules\User\Phone;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Validation\Rule;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring Users from the old DB';

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
     * @return int
     */
    public function handle()
    {
        $dateNow = Carbon::now();
        $dateStart = Carbon::createFromFormat('Y-m-d','1950-01-01');

        $this->newLine();
        $this->info('=== Начат процесс переноса Пользователей ===');

        User::truncate();
        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_users')->count();

        $this->line('Количество записей для переноса: ' . $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $dbOld->table('dvlp_users')
            ->select(
                'id',
                \DB::raw('trim(first_name) as first_name'),
                \DB::raw('trim(last_name) as last_name'),
                'birthday',
                \DB::raw('trim(email) as email'),
                \DB::raw('trim(phone) as phone'),
                'region_id',
                'city_id',
                'password_hash',
                'created_at',
                'updated_at'
            )
            ->orderBy('id')
            ->chunk(100, function ($rows) use ($progressBar, $dateNow, $dateStart) {
                foreach ($rows as $row) {

                    $birthday = null;
                    $email = null;
                    $phone = null;

                    if ($row->birthday !== null) {
                        $birthday = Carbon::createFromFormat('Y-m-d', $row->birthday)
                            ->between($dateStart, $dateNow) ? $row->birthday : null;
                    }

                    if (!empty($row->email) && strlen($row->email) < 64) {
                        $email = Email::clean($row->email);
                        $email = \Validator::make(
                            ['email' => $email],
                            ['email' => [new Email(), Rule::unique(User::class, 'email')]]
                        )->fails() ? null : $email;
                    }

                    if (!empty($row->phone)) {
                        $phone = Phone::clean($row->phone);
                        $phone = \Validator::make(
                            ['phone' => $phone],
                            ['phone' => [Rule::unique(User::class, 'phone')]]
                        )->fails() ? null : $phone;
                    }

                    $data = [
                        'id' => $row->id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'first_name' => $row->first_name,
                        'last_name' => $row->last_name,
                        'email' => $email,
                        'phone' => $phone,
                        'birth_date' => $birthday,
                        'password' => $row->password_hash,
                        'region_id' => $row->region_id > 94 ? null : $row->region_id,
                        'city_id' => $row->city_id,
                    ];

                    \DB::table('users')->insert($data);
                }

                $progressBar->advance(100);
            })
        ;

        \DB::disconnect('pgsql_old');

        $maxID = \DB::table('users')->max('id');
        $rawSelect = sprintf("SELECT setval('users_id_seq', %d)", $maxID);
        \DB::unprepared($rawSelect);

        $progressBar->finish();

        $this->newLine();
        $this->info('*** Перенос Пользователей закончен. ***');
        $this->newLine();

        return CommandAlias::SUCCESS;
    }
}
