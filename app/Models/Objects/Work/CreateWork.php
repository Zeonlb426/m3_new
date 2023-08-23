<?php

declare(strict_types=1);

namespace App\Models\Objects\Work;

use App\Models\Competition\Competition;
use App\Models\Competition\Theme;
use App\Models\Competition\WorkType;
use Carbon\Carbon;

/**
 * Class CreateWork
 * @package App\Models\Objects\Work
 *
 * @psalm-type WorkAudio = array{audio: \Illuminate\Http\UploadedFile}
 * @psalm-type WorkVideo = array{video: \App\Contracts\Objects\SocialVideoLinkInterface}
 * @psalm-type WorkImage = array{image: \Illuminate\Http\UploadedFile}
 * @psalm-type WorkText = array{text: string}
 * @psalm-type WorkImages = array{images: \Illuminate\Http\UploadedFile[]}
 * @psalm-type WorkVideoText = WorkVideo&WorkText
 * @psalm-type WorkImageText = WorkImage&WorkText
 *
 * @template T of WorkAudio|WorkVideo|WorkImage|WorkText|WorkImages|WorkVideoText|WorkImageText
 */
final class CreateWork
{
    /**
     * CreateWork constructor.
     *
     * @param \App\Models\Competition\Competition $competition
     * @param \App\Models\Competition\WorkType $workType
     * @param \App\Models\Competition\Theme|null $theme
     * @param array&T $content
     * @param string $authorName
     * @param \Carbon\Carbon $authorBirthDate
     */
    public function __construct(
        public readonly Competition $competition,
        public readonly WorkType $workType,
        public readonly ?Theme $theme,
        public readonly array $content,
        public readonly string $authorName,
        public readonly Carbon $authorBirthDate,
    ) {
    }
}
