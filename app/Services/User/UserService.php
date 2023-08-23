<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\User\ActionType;
use App\Exceptions\User\UserEmailAlreadyExistsException;
use App\Models\Objects\User\RegisterUser;
use App\Models\Objects\User\UpdateUser;
use App\Models\User;
use App\Repositories\User\UserTotalCreditRepository;
use App\Repositories\UserRepository;
use App\Services\ActivityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Support\TemporaryDirectory as TemporaryDirectorySupport;

final class UserService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UserTotalCreditRepository $userTotalCredits,
        private readonly ActivityService $activityService,
    ) {
    }

    /**
     * @param \App\Models\Objects\User\RegisterUser $registerUser
     *
     * @return \App\Models\User
     *
     * @throws \Throwable
     */
    public function register(RegisterUser $registerUser): User
    {
        $user = $this->users->transaction(function () use ($registerUser): User {
            $user = $this->users->create($registerUser);

            $this->userTotalCredits->create($user);

            return $user;
        });

        $this->activityService->addAction($user, $user, ActionType::REGISTRATION);

        return $user;
    }

    /**
     * @param array{
     *     first_name: ?string,
     *     last_name: ?string,
     *     email: ?string,
     *     birth_date: ?\Carbon\Carbon,
     * } $attributes
     *
     * @return \App\Models\User
     *
     * @throws \Throwable
     */
    public function registerForSocial(array $attributes): User
    {
        try {
            $user = $this->users->transaction(function () use ($attributes): User {
                $user = $this->users->createForSocial($attributes);

                $this->userTotalCredits->create($user);

                return $user;
            });

            $this->activityService->addAction($user, $user, ActionType::REGISTRATION);
        } catch (UserEmailAlreadyExistsException) {
            $user = $this->users->findOneBy(['email' => $attributes['email']]);
        }

        return $user;
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Objects\User\UpdateUser $updateUser
     *
     * @return \App\Models\User
     *
     * @throws \Throwable
     */
    public function update(User $user, UpdateUser $updateUser): User
    {
        return $this->users->update($user, $updateUser);
    }

    /**
     * @param \App\Models\User $user
     * @param \Illuminate\Http\UploadedFile $avatar
     *
     * @return \App\Models\User
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function updateAvatar(User $user, UploadedFile $avatar): User
    {
        $tmpDir = TemporaryDirectorySupport::create();

        $resultAvatarPath = $tmpDir->path(Str::random(32) . '.' . $avatar->extension());

        try {
            Image::load($avatar->getRealPath())
                ->fit(Manipulations::FIT_CONTAIN, 200, 200)
                ->save($resultAvatarPath)
            ;

            $user
                ->addMedia($resultAvatarPath)
                ->toMediaCollection(User::AVATAR_COLLECTION)
            ;
        } finally {
            $tmpDir->delete();
        }

        return $user;
    }
}
