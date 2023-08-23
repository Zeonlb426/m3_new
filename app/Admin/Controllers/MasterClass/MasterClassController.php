<?php
/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

namespace App\Admin\Controllers\MasterClass;

use App\Admin\Controllers\Api\DropDownListLoaderController;
use App\Enums\LoaderType;
use App\Enums\MasterClass\AdditionalSign;
use App\Models\AgeGroup;
use App\Models\Lead;
use App\Models\MasterClass\Course;
use App\Models\MasterClass\MasterClass;
use App\Models\Objects\VkLink;
use App\Rules\VkLink as VkLinkValidator;
use App\Rules\YoutubeLink as YoutubeLinkValidator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class MasterClassController extends AdminController
{
    public function __construct(
        private readonly MasterClass $model
    ) {
        $this->title = \__('admin.titles.master-classes');
    }

    public function destroy($id)
    {
        // отключаем возможность вызвать forceDelete из админки
        $masterClasses = MasterClass::query()
            ->withoutTrashed()
            ->findOrFail(Str::of($id)->explode(',')->collect()->filter(), ['id'])
        ;

        return parent::destroy($masterClasses->pluck('id')->join(','));
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
            ->orderByDesc('created_at')
            ->with(['lead', 'ageGroup'])
            ->withoutTrashed()
        ;

        $grid->disableExport();
        $grid->disableBatchActions()->disableColumnSelector();

        $grid->filter(function (Grid\Filter $filter) {
            $filter->ilike('title', \__('admin.models.master_class.title'));
            $filter->ilike('content', \__('admin.models.master_class.content'));
            $filter->ilike('video_link', \__('admin.models.master_class.video_link'));
            $filter
                ->where(function ($qb) {
                    /* @var $this Grid\Filter\Where */
                    /* @var $qb \Illuminate\Database\Eloquent\Builder */
                    $value = \array_filter((array)$this->input);

                    if (false === empty($value)) {
                        $qb->where(
                            fn($query) => /* @var $query \App\Models\MasterClass\MasterClass */
                            \array_map(fn($el) => $query->signHas($el), $value)
                        );
                    }
                }, \__('admin.models.master_class.additional_signs'), 'signs')
                ->multipleSelect(AdditionalSign::labels())
            ;
            $filter
                ->in('age_group_id', \__('admin.models.master_class.age_group'))
                ->multipleSelect(function ($selectedValues) {
                    /* @var $this Grid\Filter\In */
                    if (!empty($selectedValues)) {
                        return AgeGroup::query()->whereKey($selectedValues)->orderBy('min_age')->orderBy('max_age')->pluck('title', 'id')->toArray();
                    }
                    return [];
                })
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::AGE_GROUPS->value]))
            ;
            $filter
                ->in('lead_id', \__('admin.models.master_class.lead'))
                ->multipleSelect(function ($selectedValues) {
                    /* @var $this Grid\Filter\In */
                    if (!empty($selectedValues)) {
                        return Lead::query()->whereKey($selectedValues)->pluck('name', 'id')->toArray();
                    }
                    return [];
                })
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::LEADS->value]))
            ;
            $filter
                ->where(function ($qb) {
                    /* @var $this Grid\Filter\Where */
                    /* @var $qb \Illuminate\Database\Eloquent\Builder */
                    $value = \array_filter((array)$this->input);

                    if (false === empty($value)) {
                        $qb->whereHas(
                            'courses',
                            fn($builder) => /* @var $builder \Illuminate\Database\Eloquent\Builder */
                            $builder->whereKey($value)
                        );
                    }
                }, \__('admin.models.master_class.courses'), 'courses')
                ->multipleSelect(function ($selectedValues) {
                    /* @var $this Grid\Filter\In */
                    if (!empty($selectedValues)) {
                        return Course::query()->whereKey($selectedValues)->pluck('name', 'id')->toArray();
                    }
                    return [];
                })
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::COURSES->value]))
            ;
            $filter
                ->equal('visible_status', \__('admin.models.visible_status'))
                ->select([
                    '1' => \__('admin.switch_visible_statuses.on.text'),
                    '0' => \__('admin.switch_visible_statuses.off.text'),
                ])
            ;
            $filter->between('created_at', \__('admin.created_at'))->datetime();
        });

        $grid->column('visible_status', \__('admin.models.visible_status'))
            ->switch(\__('admin.switch_grid_visible_statuses'))
            ->sortable()
        ;
        $grid->column('title', \__('admin.models.master_class.title'))->editable();

        $grid->column('lead.name', \__('admin.models.master_class.lead'));
        $grid
            ->column('ageGroup.title', \__('admin.models.master_class.age_group'))
            ->default(\__('admin.messages.empty_value'))
        ;
        $grid->column('video_link', \__('admin.models.master_class.video_link'))->link();
        $grid->column('signs', \__('admin.models.master_class.additional_signs'))
            ->display(function () {
                /* @var $this MasterClass */
                $data = [];
                foreach ($this->signs as $sign) {
                    $data[] = \sprintf('<span class="btn btn-primary disabled" style="cursor: default">%s</span>', $sign->value);
                }

                return \implode($data);
            })
        ;

        $grid->column('likes_total_count', \__('admin.models.master_class.likes'))->style('text-align:center')->sortable();
        $grid->column('image', \__('admin.models.master_class.image'))->image();


        return $grid;
    }

    protected function form(): Form
    {
        $form = new Form($this->model);

        $form->disableCreatingCheck()->disableEditingCheck()->disableViewCheck();
        $form->builder()->getFooter()->disableReset();

        $id = $form->isEditing()
            ? (int)\Route::current()->parameter('master_class')
            : null;
        $model = isset($id)
            ? $this->model::query()->findOrFail($id)
            : null;

        $form->tab(
            \__('admin.tabs.main-info'),
            function (Form $form) use ($model) {

                $form->row(function (Form\Row $form) {
                    $form->width(4);
                    /* @var $form Form */

                    $form
                        ->switch('visible_status', \__('admin.models.visible_status'))
                        ->states(\__('admin.switch_visible_statuses'))
                        ->default(true)
                    ;
                    $form->number('order_column', \__('admin.models.order_column'));
                });

                $form->row(function (Form\Row $form) use ($model) {
                    $form->width(4);
                    /* @var $form Form */

                    $form
                        ->text('title', \__('admin.models.master_class.title'))
                        ->rules(['string', 'max:255'])
                        ->required()
                    ;
                    $form
                        ->url('video_link', \__('admin.models.master_class.video_link'))
                        ->rules([
                            'nullable',
                            'url',
                            'max:255',
                            Rule::when(
                                function (Fluent $fluent) {
                                    /** @var \Illuminate\Support\Fluent<string, string> $fluent */
                                    $link = $fluent->get('video_link');
                                    return null !== $link && VkLink::maybeIsVkLink($link);
                                },
                                [new VkLinkValidator],
                                [new YoutubeLinkValidator],
                            ),
                        ])
                    ;

                    $form->multipleSelect('signs', \__('admin.models.master_class.additional_signs'))
                        ->options(AdditionalSign::labels())
                        ->value(
                            false === empty($model?->signs)
                                ? \array_column($model->signs, 'value')
                                : null
                        )
                    ;
                });

                $form->row(function (Form\Row $form) {
                    $form->width(4);
                    /* @var $form Form */

                    $form
                        ->select('age_group_id', \__('admin.models.master_class.age_group'))
                        ->config('minimumInputLength', 0)
                        ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::AGE_GROUPS->value]))
                        ->options(function ($_, Form\Field\Select $select) {
                            /* @var $this MasterClass */

                            $olds = \old('age_group_id');
                            if (null !== $olds) {
                                $values = AgeGroup::query()->whereKey($olds)->orderBy('min_age')->orderBy('max_age')->pluck('title', 'id')->toArray();
                            } else {
                                if (isset($this->ageGroup)) {
                                    $values = [
                                        $this->ageGroup->getKey() => $this->ageGroup->title,
                                    ];
                                } else {
                                    $values = [];
                                }
                            }
                            $select->value(\Arr::first(\array_keys($values)));

                            return $values;
                        })
                    ;

                    $form
                        ->select('lead_id', \__('admin.models.master_class.lead'))
                        ->config('minimumInputLength', 0)
                        ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::LEADS->value]))
                        ->options(function ($_, Form\Field\Select $select) {
                            /* @var $this MasterClass */

                            $olds = \old('lead_id');
                            if (null !== $olds) {
                                $values = Lead::query()->whereKey($olds)->pluck('name', 'id')->toArray();
                            } else {
                                if (isset($this->lead)) {
                                    $values = [
                                        $this->lead->getKey() => $this->lead->name,
                                    ];
                                } else {
                                    $values = [];
                                }
                            }
                            $select->value(\Arr::first(\array_keys($values)));

                            return $values;
                        })
                    ;
                    $form->multipleSelect('courses', \__('admin.models.master_class.courses'))
                        ->config('minimumInputLength', 0)
                        ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::COURSES->value]))
                        ->options(function ($_, Form\Field\MultipleSelect $select) {
                            /* @var $this MasterClass */

                            $olds = \old('courses');
                            if (null !== $olds) {
                                $values = Course::query()->whereKey($olds)->pluck('name', 'id')->toArray();
                            } else {
                                $values = $this->courses->pluck('name', 'id')->toArray();
                            }
                            $select->value(\array_keys($values));

                            return $values;
                        })
                    ;
                });

                $form->row(function (Form\Row $form) {
                    /* @var $form Form */

                    $form
                        ->ckeditor('content', \__('admin.models.master_class.content'))
                        ->rules(['nullable', 'string', 'max:' . (2 ** 31)])
                    ;
                    $form
                        ->mediaLibrary('image', \__('admin.models.master_class.image'))
                        ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
                    ;
                });

                if ($form->isEditing()) {
                    $form->row(function (Form\Row $form) {
                        $form->width(4);
                        /* @var $form Form */

                        $form->text('likes_total_count', \__('admin.models.master_class.likes'))->disable();
                        $form->text('created_at', \__('admin.created_at'))->disable();
                        $form->text('updated_at', \__('admin.updated_at'))->disable();
                    });
                }
            }
        );
        $form->tab(
            \__('admin.tabs.sharing-info'),
            function (Form $form) {
                $form->text('sharing.title', \__('admin.models.sharing.title'));
                $form->ckeditor('sharing.description', \__('admin.models.sharing.description'));
                $form
                    ->mediaLibrary('sharing.image', \__('admin.models.sharing.image'))
                    ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
                ;
            }
        );

        $form->ignore(['likes_total_count', 'sharing']);

        $form->saved(function (Form $form) {

            if (1 === \count(\Arr::except(\request()->all(), ['_token', '_method']))) {
                return true;
            }

            $shareData = \array_filter(\request('sharing', []));

            if (false === empty($shareData)) {
                /* @var $model MasterClass */
                $model = $form->model();

                $model->load('sharing');
                $sharing = $model->sharing;

                if (null === $sharing) {
                    /* @var $sharing \App\Models\Sharing */
                    $sharing = $model->sharing()->create(\Arr::except($shareData, 'image'));
                }

                if (isset($shareData['image']) && $shareData['image'] instanceof UploadedFile) {
                    $sharing->addMedia($shareData['image'])->toMediaCollection($sharing::IMAGE_COLLECTION);
                }
            }

            return true;
        });

        return $form;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail(string|int $id): Show
    {
        $model = $this->model::query()
            ->with(['sharing', 'lead', 'ageGroup', 'courses'])
            ->findOrFail($id)
        ;
        $show = new Show($model);

        $show->field('id', \__('admin.models.id'));
        $show
            ->field('visible_status', \__('admin.models.visible_status'))
            ->as(fn($status) => $status
                ? \__('admin.switch_visible_statuses.on.text')
                : \__('admin.switch_visible_statuses.off.text')
            )
        ;
        $show->divider();
        $show->field('title', \__('admin.models.master_class.title'));
        $show->field('video_link', \__('admin.models.master_class.video_link'))->link();
        $show->field('order_column', \__('admin.models.order_column'));
        $show->divider();
        $show
            ->field('signs', \__('admin.models.news.additional_signs'))
            ->as(
                fn(?array $signs) => \implode(\array_map(
                    fn(AdditionalSign $sign) => \sprintf('<span class="btn btn-primary disabled" style="cursor: default">%s</span>', $sign->value),
                    $signs
                ))
            )
            ->unescape()
        ;
        $show->field('content', \__('admin.models.master_class.content'))->unescape();
        $show->field('image', \__('admin.models.master_class.image'))->image();
        $show->divider();
        $show->field('ageGroup.title', \__('admin.models.master_class.age_group'));
        $show->field('lead.name', \__('admin.models.master_class.lead'));
        $show
            ->field('courses', \__('admin.models.master_class.courses'))
            ->as(function () {
                /* @var $this MasterClass */
                return $this->courses->pluck('name')->implode('<br>');
            })
            ->unescape()
        ;
        $show->divider();
        $show->field('likes_total_count', \__('admin.models.master_class.likes'));
        $show->field('created_at', \__('admin.created_at'));
        $show->field('updated_at', \__('admin.updated_at'));
        $show->divider();
        $show->field('sharing.title', \__('admin.models.sharing.title'));
        $show->field('sharing.description', \__('admin.models.sharing.description'))->unescape();
        $show->field('sharing.image', \__('admin.models.sharing.image'))->image();

        return $show;
    }
}
