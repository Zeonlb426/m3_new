<?php

declare(strict_types=1);

namespace App\Services\Competition;

use App\Enums\Competition\WorkContentType;
use App\Enums\Competition\WorkTypeSlug;
use App\Enums\CompetitionWork\ApproveStatus;
use App\Models\CompetitionWork\Work;
use App\Models\Objects\Work\CreateWork;
use App\Models\User;
use App\Repositories\CompetitionWork\WorkAuthorRepository;
use App\Repositories\CompetitionWork\WorkRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Support\TemporaryDirectory as TemporaryDirectorySupport;
use Spatie\TemporaryDirectory\TemporaryDirectory;

/**
 * Class WorkService
 * @package App\Services\Competition
 */
final class WorkService
{
    private ?TemporaryDirectory $tmpDir = null;

    public function __construct(
        private readonly WorkRepository $works,
        private readonly WorkAuthorRepository $workAuthors,
    ) {

    }

    /**
     * @param \App\Models\User $user
     * @param \App\Models\Objects\Work\CreateWork $createWork
     *
     * @return \App\Models\CompetitionWork\Work
     *
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     * @throws \Throwable
     */
    public function create(User $user, CreateWork $createWork): Work
    {
        $work = new Work();

        $work->status = ApproveStatus::PENDING;

        $work->user()->associate($user);
        $work->competition()->associate($createWork->competition);
        $work->workType()->associate($createWork->workType);
        $work->theme()->associate($createWork->theme);

        $this->tmpDir = TemporaryDirectorySupport::create();

        try {
            $work = $this->fillWorkContent($work, $createWork);

            $this->works->transaction(function () use ($user, $work, $createWork): void {
                $author = $this->workAuthors->firstOrCreate(
                    $user, $createWork->authorName, $createWork->authorBirthDate,
                );

                $work->author()->associate($author);

                $work->save();
            });
        } finally {
            $this->tmpDir->delete();
            $this->tmpDir = null;
        }

        return $work;
    }

    /**
     * @param \App\Models\CompetitionWork\Work $work
     * @param \App\Models\Objects\Work\CreateWork $createWork
     *
     * @return \App\Models\CompetitionWork\Work
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    private function fillWorkContent(Work $work, CreateWork $createWork): Work
    {
        $workTypeSlug = WorkTypeSlug::from($createWork->workType->slug);

        foreach ($workTypeSlug->allowedContentTypes() as $allowedContentType) {
            switch ($allowedContentType) {
                case WorkContentType::AUDIO:
                    $work
                        ->addMedia($createWork->content[WorkContentType::AUDIO->value])
                        ->toMediaCollection(Work::AUDIO_COLLECTION)
                    ;
                    break;
                case WorkContentType::VIDEO:
                    /** @var \App\Contracts\Objects\SocialVideoLinkInterface $video */
                    $video = $createWork->content[WorkContentType::VIDEO->value];

                    $work->work_video_type = $video->getVideoType();
                    $work->work_video = $video->__toString();
                    $work->work_video_id = $video->getVideoId();

                    break;
                case WorkContentType::TEXT:
                    $work->work_text = $createWork->content[WorkContentType::TEXT->value];
                    break;
                case WorkContentType::IMAGE:
                    $work
                        ->addMedia($createWork->content[WorkContentType::IMAGE->value])
                        ->toMediaCollection($work::IMAGE_COLLECTION)
                    ;
                    $this->createWorkPreview($work, $createWork->content[WorkContentType::IMAGE->value]);
                    break;
                case WorkContentType::IMAGES:
                    foreach ($createWork->content[WorkContentType::IMAGES->value] as $image) {
                        $work->addMedia($image)->toMediaCollection($work::IMAGE_COLLECTION);
                    }
                    $this->createWorkPreview($work, Arr::first($createWork->content[WorkContentType::IMAGES->value]));
                    break;
                default:
                    throw new RuntimeException(\sprintf(
                        'Not implemented for type "%s".', $allowedContentType->value,
                    ));
            }
        }

        return $work;
    }

    /**
     * @param \App\Models\CompetitionWork\Work $work
     * @param \Illuminate\Http\UploadedFile|string $previewSource
     *
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function createWorkPreview(Work $work, $previewSource): void
    {
        $hasExternalTmpDir = null !== $this->tmpDir;

        $tmpDir = $this->tmpDir ?? TemporaryDirectorySupport::create();

        $previewSourcePath = $previewSource instanceof UploadedFile ? $previewSource->getRealPath() : $previewSource;
        $previewSourceExt = \pathinfo($previewSourcePath, \PATHINFO_EXTENSION);
        $previewSourceExt = '' !== $previewSourceExt ? $previewSourceExt : 'blob';

        $previewPath = $tmpDir->path(Str::random(32) . '.' . $previewSourceExt);

        Image::load($previewSourcePath)
            ->fit(Manipulations::FIT_MAX, 800, 0)
            ->save($previewPath)
        ;

        $work
            ->addMedia($previewPath)
            ->toMediaCollection(Work::PREVIEW_COLLECTION)
        ;

        if (false === $hasExternalTmpDir) {
            $tmpDir->delete();
        }
    }
}
