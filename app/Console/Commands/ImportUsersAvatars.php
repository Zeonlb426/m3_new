<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Support\TemporaryDirectory;

final class ImportUsersAvatars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:avatars';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transferring Users Avatars from the old DB';

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
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(): int
    {
        $baseURL = 'https://pokolenie.mts.ru/uploads/original';
        $arrayExt = ['jpg', 'jpeg', 'png', 'gif'];

        $this->newLine();
        $this->info('=== Начат процесс переноса Аватарок пользователей ===');

        $dbOld = \DB::connection('pgsql_old');

        $count = $dbOld->table('dvlp_users')
            ->where('avatar', 'LIKE', 'http%')
            ->orWhere('avatar', 'LIKE', '/%')
            ->count()
        ;

        $this->line('Количество записей для переноса: ' . $count);
        $this->line('Идёт перенос данных ...');

        $progressBar = $this->output->createProgressBar($count);

        $tmpDir = TemporaryDirectory::create();

        $dbOld->table('dvlp_users')
            ->select(
                'id',
                \DB::raw('trim(avatar) as avatar'),
            )
            ->where('avatar', 'LIKE', 'http%')
            ->orWhere('avatar', 'LIKE', '/%')
            ->orderBy('id')
            ->chunk(10, function ($rows) use ($progressBar, $baseURL, $tmpDir, $arrayExt) {
                foreach ($rows as $row) {
                    if (Str::isEmpty($row->avatar)) {
                        $progressBar->advance();

                        continue;
                    }

                    $ext = \pathinfo($row->avatar, \PATHINFO_EXTENSION);

                    if (empty($ext) || false === \in_array($ext, $arrayExt)) {
                        $progressBar->advance();

                        continue;
                    }

                    $user = User::whereId($row->id)->first();

                    if (null === $user || $user->hasMedia(User::AVATAR_COLLECTION)) {
                        $progressBar->advance();

                        continue;
                    }

                    $tmpAvatar = $tmpDir->path(
                        'import/avatars/' . \uniqid(more_entropy: true) . '.' . $ext
                    );

                    if (\mb_substr($row->avatar, 0, 1) === '/'
//                        && Storage::disk('old_uploads')->exists($row->avatar)
                    ) {
                        $progressBar->advance();

                        continue;

                        $fullPath = \config('filesystems.disks.old_uploads.root')
                            . \DIRECTORY_SEPARATOR
                            . $row->avatar;

                        $size = \getimagesize($fullPath);

                        if (false === $size) {
                            $this->line('invalid image for row with id=' . $row->id);
                            $progressBar->advance();

                            continue;
                        }

                        if ($size[0] > 200 || $size[1] > 200) {
                            Image::load($fullPath)
                                ->fit(Manipulations::FIT_CONTAIN, 200, 200)
                                ->save($tmpAvatar)
                            ;
                        }

                        $user
                            ->addMediaFromDisk($row->avatar, 'old_uploads')
                            ->toMediaCollection(User::AVATAR_COLLECTION)
                        ;

//                        $progressBar->advance();
//
//                        continue;
                    }

                    $url = \mb_substr($row->avatar, 0, 1) === '/' ? $baseURL . $row->avatar : $row->avatar;

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $image = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                    curl_close($ch);

                    if ($httpCode !== 200 || !is_string($image) || $fileSize == 0) {
                        $progressBar->advance();

                        continue;
                    }

                    $fp = \fopen($tmpAvatar, "w");
                    \fwrite($fp, $image);
                    \fclose($fp);

                    $size = \getimagesize($tmpAvatar);

                    if (false === $size) {
                        $this->line('invalid image for row with id=' . $row->id);
                        $progressBar->advance();

                        continue;
                    }

                    if ($size[0] > 200 || $size[1] > 200) {
                        Image::load($tmpAvatar)
                            ->fit(Manipulations::FIT_CONTAIN, 200, 200)
                            ->save()
                        ;
                    }

                    $user->addMedia($tmpAvatar)->toMediaCollection(User::AVATAR_COLLECTION);

                    $progressBar->advance();
                }
            })
        ;

        $tmpDir->delete();

        \DB::disconnect('pgsql_old');

        $progressBar->finish();

        $this->newLine();
        $this->info('*** Перенос Аватарок пользователей закончен. ***');
        $this->newLine();

        return self::SUCCESS;
    }
}
