<?php

declare(strict_types=1);

namespace App\Enums\Competition;

use App\Enums\Traits\EnumToArray;
use OpenApi\Attributes as OA;

/**
 * Class WorkTypeSlug
 * @package App\Enums\Competition
 */
#[OA\Schema(
    schema: 'WorkTypeSlugField',
    type: 'string',
    enum: [
        self::AUDIO,
        self::VIDEO,
        self::TEXT,
        self::IMAGE,
        self::IMAGES,
        self::VIDEO_TEXT,
        self::IMAGE_TEXT,
    ],
    description: <<<STR
Типы работ:
<li>audio - в одноимённом поле ожидает аудиофайл</li>
<li>video - в одноимённом поле ожидает ссылку на видео вк или ютуб</li>
<li>video_text - в поле video ожидает ссылку на видео вк или ютуб, в поле text ожидает текст/li>
<li>text - в одноимённом поле ожидает текстовый контент</li>
<li>image - в одноимённом поле ожидает файл изображение</li>
<li>image_text - в поле image ожидает файл-изображение, в поле text ожидает текст</li>
<li>images - в одноимённом поле ожидает несколько файлов изображений</li>
STR,
)]
enum WorkTypeSlug: string
{
    use EnumToArray;

    case AUDIO = 'audio';
    case VIDEO = 'video';
    case TEXT = 'text';
    case IMAGE = 'image';
    case IMAGES = 'images';
    case VIDEO_TEXT = 'video_text';
    case IMAGE_TEXT = 'image_text';

    public function isAllowedContentTypes(WorkContentType ...$contentTypes): bool
    {
        foreach ($contentTypes as $contentType) {
            if (\in_array($contentType, $this->allowedContentTypes())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<\App\Enums\Competition\WorkContentType>
     */
    public function allowedContentTypes(): array
    {
        return match ($this) {
            self::AUDIO => [WorkContentType::AUDIO],
            self::VIDEO => [WorkContentType::VIDEO],
            self::TEXT => [WorkContentType::TEXT],
            self::IMAGE => [WorkContentType::IMAGE],
            self::IMAGES => [WorkContentType::IMAGES],
            self::VIDEO_TEXT => [WorkContentType::VIDEO, WorkContentType::TEXT],
            self::IMAGE_TEXT => [WorkContentType::IMAGE, WorkContentType::TEXT],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::AUDIO => \__('admin.models.work.audio.content'),
            self::VIDEO => \__('admin.models.work.video.content'),
            self::TEXT => \__('admin.models.work.text.content'),
            self::IMAGE => \__('admin.models.work.image.content'),
            self::IMAGES => \__('admin.models.work.images.content'),
            self::VIDEO_TEXT => \__('admin.models.work.video_text.content'),
            self::IMAGE_TEXT => \__('admin.models.work.image_text.content'),
        };
    }
}
