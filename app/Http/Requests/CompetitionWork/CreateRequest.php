<?php

declare(strict_types=1);

namespace App\Http\Requests\CompetitionWork;

use App\Enums\Competition\WorkContentType;
use App\Enums\Competition\WorkTypeSlug;
use App\Http\Requests\ApiRequest;
use App\Models\Competition\Competition;
use App\Models\Competition\Theme;
use App\Models\Competition\WorkType;
use App\Models\Objects\VkLink;
use App\Models\Objects\Work\CreateWork;
use App\Models\Objects\YoutubeLink;
use App\Repositories\Competition\CompetitionRepository;
use App\Rules\VkLink as VkLinkValidator;
use App\Rules\YoutubeLink as YoutubeLinkValidator;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class CreateRequest
 * @package App\Http\Requests\CompetitionWork
 */
#[OA\Schema(
    schema: 'WorkCreateRequest',
    type: 'object',
    required: ['author[name]', 'author[birth_date]', 'work_type_slug'],
    properties: [
        new OA\Property(property: 'work_type_slug', ref: '#/components/schemas/WorkTypeSlugField', nullable: false),
        new OA\Property(property: 'theme_id', type: 'string', nullable: true, description: 'ID темы'),

        new OA\Property(
            property: 'author',
            type: 'object',
            required: ['name', 'birth_date'],
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 255, nullable: false, description: 'Имя'),
                new OA\Property(property: 'birth_date', type: 'string', format: 'date', example: '1990-01-31', nullable: false, description: 'Дата рождения'),
            ],
        ),

        new OA\Property(property: 'audio', type: 'string', format: 'binary', nullable: true, description: 'Аудиофайл'),
        new OA\Property(property: 'video', type: 'string', nullable: true, description: 'Ссылка на видео'),
        new OA\Property(property: 'text', type: 'string', nullable: true, description: 'Тестовый контент'),
        new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true, description: 'Изображение'),
        new OA\Property(
            property: 'images',
            type: 'array',
            items: new OA\Items(type: 'string', format: 'binary'),
            nullable: true,
            description: 'Изображения',
        ),
    ],
)]
final class CreateRequest extends ApiRequest
{
    private Competition $competition;
    private WorkType $workType;
    private ?Theme $theme = null;

    protected function prepareForValidation(): void
    {
        $competition = $this->container[CompetitionRepository::class]->createApiQuery(
            needResourceContent: false,
        )->whereSlug($this->route('slug'))->first();

        if (null === $competition) {
            throw new BadRequestHttpException();
        }

        $this->competition = $competition;

        $validator = \validator([
            'work_type_slug' => $this->request->get('work_type_slug'),
        ], [
            'work_type_slug' => [
                'required', 'string', Rule::enum(WorkTypeSlug::class), $this->validateWorkType(...),
            ],
        ]);

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }
    }

    public function rules(): array
    {
        $workTypeSlug = WorkTypeSlug::from($this->workType->slug);

        return [
            'theme_id' => ['bail', 'nullable', 'int', $this->validateTheme(...)],
            'author.name' => ['required', 'string', 'max:255'],
            'author.birth_date' => [
                'required',
                'date',
                'date_format:Y-m-d',
                \sprintf('after:%s', \now()->subYears(150)->format('Y-m-d')),
                \sprintf('before:%s', \now()->format('Y-m-d')),
                $this->validateAge(...),
            ],
            WorkContentType::AUDIO->value => [
                Rule::requiredIf($workTypeSlug->isAllowedContentTypes(WorkContentType::AUDIO)),
                Rule::excludeIf(false === $workTypeSlug->isAllowedContentTypes(WorkContentType::AUDIO)),
                'file',
                'mimetypes:audio/*',
                'mimes:mp3,aac,m4a,ogg',
                'max:' . (5 * 1024),
            ],
            WorkContentType::VIDEO->value => [
                Rule::requiredIf($workTypeSlug->isAllowedContentTypes(WorkContentType::VIDEO)),
                Rule::excludeIf(false === $workTypeSlug->isAllowedContentTypes(WorkContentType::VIDEO)),
                'string',
                'max:255',
                Rule::when(
                    function (Fluent $fluent): bool {
                        /** @var \Illuminate\Support\Fluent<string, string> $fluent */
                        $link = $fluent->get(WorkContentType::VIDEO->value);
                        return null !== $link && VkLink::maybeIsVkLink($link);
                    },
                    [new VkLinkValidator()],
                    [new YoutubeLinkValidator()],
                ),
            ],
            WorkContentType::TEXT->value => [
                Rule::requiredIf($workTypeSlug->isAllowedContentTypes(WorkContentType::TEXT)),
                Rule::excludeIf(false === $workTypeSlug->isAllowedContentTypes(WorkContentType::TEXT)),
                'string',
                'max:' . (2 ** 31),
            ],
            WorkContentType::IMAGE->value => [
                Rule::requiredIf($workTypeSlug->isAllowedContentTypes(WorkContentType::IMAGE)),
                Rule::excludeIf(false === $workTypeSlug->isAllowedContentTypes(WorkContentType::IMAGE)),
                Rule::imageFile()
                    ->rules(['mimetypes:image/jpeg,image/png,image/gif,image/webp'])
                    ->max(5 * 1024)
                ,
            ],
            WorkContentType::IMAGES->value => [
                Rule::requiredIf($workTypeSlug->isAllowedContentTypes(WorkContentType::IMAGES)),
                Rule::excludeIf(false === $workTypeSlug->isAllowedContentTypes(WorkContentType::IMAGES)),
                'array',
                'max:10',
            ],
            WorkContentType::IMAGES->value . '.*' => [
                'required',
                Rule::imageFile()
                    ->rules(['mimetypes:image/jpeg,image/png,image/gif,image/webp'])
                    ->max(5 * 1024)
                ,
            ],
        ];
    }

    /**
     * @param string $attr
     * @param string $value
     * @param \Closure(string $message): void $fail
     */
    public function validateWorkType(string $attr, string $value, Closure $fail): void
    {
        $workType = $this->competition
            ->workTypes()
            ->where(['slug' => $value])
            ->first()
        ;

        if (null === $workType) {
            $fail(\__('validation.exists', ['attribute' => $attr]));
        } else {
            $this->workType = $workType;
        }
    }

    /**
     * @param string $attr
     * @param int $value
     * @param \Closure(string $message): void $fail
     */
    public function validateTheme(string $attr, int $value, Closure $fail): void
    {
        if (false === $this->competition->titles_content->themesEnabled) {
            $fail('Нельзя указать тему для этой работы');

            return;
        }

        $theme = $this->competition
            ->themes()
            ->whereKey($value)
            ->first()
        ;

        if (null === $theme) {
            $fail(\__('validation.exists', ['attribute' => $attr]));
        } else {
            $this->theme = $theme;
        }
    }

    /**
     * @param string $attr
     * @param string $value
     * @param \Closure(string $message): void $fail
     */
    public function validateAge(string $attr, string $value, Closure $fail): void
    {
        $age = Carbon::parse($value)->age;

        $validAge = $this->competition
            ->ageGroupsAll()
            ->where('min_age', '<=', $age)
            ->where('max_age', '>=', $age)
            ->exists()
        ;

        if (false === $validAge) {
            $fail(\__('messages.exception.invalid_age'));
        }
    }

    public function getCreateWork(): CreateWork
    {
        $validated = $this->validated();

        $workTypeSlug = WorkTypeSlug::from($this->workType->slug);

        $content = Arr::only($validated, \array_map(
            static fn(WorkContentType $type): string => $type->value, $workTypeSlug->allowedContentTypes(),
        ));

        if (isset($content[WorkContentType::VIDEO->value])) {
            $videoLink = $content[WorkContentType::VIDEO->value];

            $content[WorkContentType::VIDEO->value] = VkLink::maybeIsVkLink($videoLink)
                ? new VkLink($videoLink)
                : new YoutubeLink($videoLink);
        }

        return new CreateWork(
            competition: $this->competition,
            workType: $this->workType,
            theme: $this->theme,
            content: $content,
            authorName: $validated['author']['name'],
            authorBirthDate: Carbon::parse($validated['author']['birth_date']),
        );
    }
}
