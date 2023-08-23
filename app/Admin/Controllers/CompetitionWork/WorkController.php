<?php
/** @noinspection PhpUndefinedMethodInspection */

namespace App\Admin\Controllers\CompetitionWork;

use App\Admin\Controllers\Api\DropDownListLoaderController;
use App\Admin\Exceptions\TooMuchWorksForExportException;
use App\Admin\Exporters\WorkExporter;
use App\Enums\Competition\WorkTypeSlug;
use App\Enums\CompetitionWork\ApproveStatus;
use App\Enums\LoaderType;
use App\Models\Competition\Competition;
use App\Models\Competition\Theme;
use App\Models\Competition\WorkType;
use App\Models\CompetitionWork\Work;
use App\Models\CompetitionWork\WorkAuthor;
use App\Models\User;
use DateTime;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use function Clue\StreamFilter\fun;
use function PHPUnit\Framework\isEmpty;

class WorkController extends AdminController
{
    public function __construct(
        private readonly Work $model
    ) {
        $this->title = \__('admin.titles.works');
    }

    public function index(Content $content)
    {
        try {
            $grid = $this->grid()->render();
        } catch (TooMuchWorksForExportException) {
            \admin_error('Ошибка экспорта', 'Нельзя выгружать слишком много записей');

            return \back();
        }

        return $content
            ->title($this->title())
            ->description($this->description['index'] ?? \trans('admin.list'))
            ->body($grid)
        ;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid($this->model);
        $grid
            ->model()
            ->with([
                'user.region',
                'user.city',
                'user.userSocial',
                'author',
                'competition.ageGroups',
                'workType',
                'theme',
            ])
            ->orderBy('created_at', 'desc')
            ->withoutTrashed()
        ;
        $grid
            ->disableColumnSelector()
            ->disableCreateButton()
            ->batchActions(function (Grid\Tools\BatchActions $actions): void {
                $actions
                    ->disableDelete()
                ;
            })
        ;

        $grid->exporter(new WorkExporter($grid));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();

            $filter
                ->in('user_id', \__('admin.models.work.user'))
                ->multipleSelect(function ($selectedValues) {
                    /* @var $this Grid\Filter\In */
                    if (!empty($selectedValues)) {
                        return User::query()->whereKey($selectedValues)->get()->pluck('name', 'id')->toArray();
                    }
                    return [];
                })
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::USERS->value]))
            ;
            $filter->ilike('author.name', \__('admin.models.work_author.name'));

            $filter->where(function ($qb) {
                /* @var $this Grid\Filter\Where */
                /* @var $qb \Illuminate\Database\Eloquent\Builder|Work */
                $value = (int)$this->input;
                if ($value) {
                    $qb->whereHas(
                        'author',
                        fn(Builder|Relation|WorkAuthor $builder) => $builder->ageIs($value)
                    );
                }
            }, \__('admin.models.work_author.age') . '(>=)', 'age_from')->integer();
            $filter->where(function ($qb) {
                /* @var $this Grid\Filter\Where */
                /* @var $qb \Illuminate\Database\Eloquent\Builder|Work */
                $value = (int)$this->input;
                if ($value) {
                    $qb->whereHas(
                        'author',
                        fn(Builder|Relation|WorkAuthor $builder) => $builder->ageIs($value, '<=')
                    );
                }
            }, \__('admin.models.work_author.age') . '(<=)', 'age_to')->integer();

            $filter
                ->in('work_type_id', \__('admin.models.work.work_type'))
                ->multipleSelect(WorkType::query()->pluck('title', 'id')->toArray())
            ;
            $filter
                ->in('theme_id', \__('admin.models.work.theme'))
                ->multipleSelect(function ($selectedValues) {
                    /* @var $this Grid\Filter\In */
                    if (!empty($selectedValues)) {
                        return Theme::query()->whereKey($selectedValues)->pluck('title', 'id')->toArray();
                    }
                    return [];
                })
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::THEMES->value]))
            ;
            $filter
                ->in('competition_id', \__('admin.models.work.competition'))
                ->multipleSelect(function ($selectedValues) {
                    /* @var $this Grid\Filter\In */
                    if (!empty($selectedValues)) {
                        return Competition::query()->whereKey($selectedValues)->pluck('title', 'id')->toArray();
                    }
                    return [];
                })
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::COMPETITIONS->value]))
            ;

            $filter
                ->equal('status', \__('admin.models.visible_status'))
                ->multipleSelect(ApproveStatus::labels())
            ;
            $filter->between('created_at', \__('admin.created_at'))->datetime();
        });

        $grid->actions(function (Grid\Displayers\DropdownActions $actions) {
            $actions->disableEdit()->disableView();
        });

        $grid->column('status', \__('admin.models.visible_status'))
            ->select(ApproveStatus::labels())
            ->sortable()
        ;

        $grid
            ->column('user.name', \__('admin.models.work.user'))
            ->display(function () {
                /* @var $this Work */

                if (empty($this->user_id)) {
                    return '-';
                }

                return \sprintf(
                    '<a href="%s" target="_blank">%s (#%s)</a>',
                    \sprintf('%s?id=%d', \route(\admin_get_route('users.index')), $this->user->getKey()),
                    $this->user->name,
                    $this->user->getKey(),
                );
            })
        ;
        $grid->column('author.name', \__('admin.models.work_author.name'));
        $grid->column('author.birth_date', \__('admin.models.work_author.age'))
            ->display(function (): string {
                /** @var Work $this */

                $birthDate = $this->author->birth_date ? new DateTime($this->author->birth_date) : 0;
                $createdWork = $this->created_at ? new DateTime($this->created_at) : 0;

                $years = '-';
                if ($birthDate && $createdWork) {
                    $interval = $birthDate->diff($createdWork);
                    $years = $interval->format('%Y лет');
                }

                return $years;
            })
            ->sortable()
        ;
        $grid->column('competition.ageGroups.title', \__('admin.models.competition.age_groups.title'))
            ->display(function (): ?string {
                /** @var Work $this */

                $ageGroups = $this->competition->ageGroups;

                if ($ageGroups->isEmpty()) {
                    return '-';
                }

                return $ageGroups->implode('title', ',<br>');
            })
        ;
        $grid->column('competition.title', \__('admin.models.work.competition'));
        $grid->column('theme.title', \__('admin.models.work.theme'));
        $grid->column('workType.title', \__('admin.models.work.work_type'));
        $grid
            ->column('user.city.name', \__('admin.models.user.city'))
            ->display(function (): ?string {
                /** @var Work $this */
                return $this->user->city?->title;
            })
        ;
        $grid->column('likes_total_count', \__('admin.models.news.likes'))->style('text-align:center')->sortable();
        $grid
            ->column('content', \__('admin.models.work.content'))
            ->display(function () {
                /* @var $this Work */

                $contentParts = [];
                if (false === empty($this->work_audio)) {
                    $contentParts[] = \__('admin.models.work.audio.title');
                    $contentParts[] = "<a href='{$this->content[WorkTypeSlug::AUDIO->value]}' target='_blank'>" . \__('admin.models.work.audio.content') . "</a>";
                }
                if (false === empty($this->work_image)) {
                    foreach ($this->work_images as $img) {
                        $contentParts[] = "<img alt='image' src='$img'  style='width:75px;height:75px;object-fit: cover' class='img img-thumbnail'/>";
                    }
                }
                if (false === empty($this->work_video_content)) {
                    $contentParts[] = \__('admin.models.work.video.title');
                    $contentParts[] = "<a href='{$this->content[WorkTypeSlug::VIDEO->value]['link']}' target='_blank'>" . \__('admin.models.work.video.content') . "</a>";
                }
                if (false === empty($this->work_text)) {
                    $contentParts[] = \__('admin.models.work.text.title');
                    $contentParts[] = $this->content[WorkTypeSlug::TEXT->value];
                }
                return \implode('<br>', $contentParts);
            })
        ;
        $grid->column('created_at', \__('admin.created_at'))->default()->sortable();

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        $form = new Form($this->model);

        $form->select('status')->options(ApproveStatus::labels());

        return $form;
    }
}
