<?php
/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

namespace App\Admin\Controllers\Competition;

use App\Admin\Controllers\Api\DropDownListLoaderController;
use App\Admin\Displayers\ExpandCompetition;
use App\Enums\Competition\TileSize;
use App\Enums\LoaderType;
use App\Models\AgeGroup;
use App\Models\Competition\Competition;
use App\Models\Competition\Partner;
use App\Models\Competition\Prize;
use App\Models\Competition\Theme;
use App\Models\Competition\WorkType;
use App\Models\Lead;
use App\Models\MasterClass\MasterClass;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Collapse;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class CompetitionController extends AdminController
{
    public function __construct(
        private readonly Competition $model
    ) {
        $this->title = \__('admin.titles.competitions');
    }

    protected function grid(): Grid
    {
        $grid = new Grid($this->model);

        $grid->disableExport();
        $grid->disableBatchActions()->disableColumnSelector();

        $grid->sortable();

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->ilike('title', \__('admin.models.competition.title'));
            $filter->ilike('short_content', \__('admin.models.competition.short_content'));
            $filter->ilike('content', \__('admin.models.competition.content'));
            $filter->ilike('slug', \__('admin.models.slug'));

            $filter
                ->where(function ($qb) {
                    /* @var $this Grid\Filter\Where */
                    /* @var $qb \Illuminate\Database\Eloquent\Builder */
                    $value = \array_filter((array)$this->input);

                    if (false === empty($value)) {
                        $qb->whereHas(
                            'workTypesAll',
                            fn($builder) => /* @var $builder \Illuminate\Database\Eloquent\Builder */
                            $builder->whereKey($value)
                        );
                    }
                }, \__('admin.models.competition.work_types.title'), 'workTypes')
                ->multipleSelect(WorkType::query()->pluck('title', 'id')->toArray())
            ;
            $filter
                ->where(function ($qb) {
                    /* @var $this Grid\Filter\Where */
                    /* @var $qb \Illuminate\Database\Eloquent\Builder */
                    $value = \array_filter((array)$this->input);

                    if (false === empty($value)) {
                        $qb->whereHas(
                            'ageGroupsAll',
                            fn($builder) => /* @var $builder \Illuminate\Database\Eloquent\Builder */
                            $builder->whereKey($value)
                        );
                    }
                }, \__('admin.models.competition.age_groups.title'), 'ageGroups')
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
                ->where(function ($qb) {
                    /* @var $this Grid\Filter\Where */
                    /* @var $qb \Illuminate\Database\Eloquent\Builder */
                    $value = \array_filter((array)$this->input);

                    if (false === empty($value)) {
                        $qb->whereHas(
                            'themes',
                            fn($builder) => /* @var $builder \Illuminate\Database\Eloquent\Builder */
                            $builder->whereKey($value)
                        );
                    }
                }, \__('admin.models.competition.themes.title'), 'themes')
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
                ->where(function ($qb) {
                    /* @var $this Grid\Filter\Where */
                    /* @var $qb \Illuminate\Database\Eloquent\Builder */
                    $value = \array_filter((array)$this->input);

                    if (false === empty($value)) {
                        $qb->whereHas(
                            'leadsAll',
                            fn($builder) => /* @var $builder \Illuminate\Database\Eloquent\Builder */
                            $builder->whereKey($value)
                        );
                    }
                }, \__('admin.models.competition.leads.title'), 'leads')
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
                            'masterClassesAll',
                            fn($builder) => /* @var $builder \Illuminate\Database\Eloquent\Builder */
                            $builder->whereKey($value)
                        );
                    }
                }, \__('admin.models.competition.master_classes.title'), 'masterClasses')
                ->multipleSelect(function ($selectedValues) {
                    /* @var $this Grid\Filter\In */
                    if (!empty($selectedValues)) {
                        return MasterClass::query()->whereKey($selectedValues)->pluck('title', 'id')->toArray();
                    }
                    return [];
                })
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::MASTER_CLASSES->value]))
            ;
            $filter
                ->where(function ($qb) {
                    /* @var $this Grid\Filter\Where */
                    /* @var $qb \Illuminate\Database\Eloquent\Builder */
                    $value = \array_filter((array)$this->input);

                    if (false === empty($value)) {
                        $qb->whereHas(
                            'partnersAll',
                            fn($builder) => /* @var $builder \Illuminate\Database\Eloquent\Builder */
                            $builder->whereKey($value)
                        );
                    }
                }, \__('admin.models.competition.partners.title'), 'partners')
                ->multipleSelect(function ($selectedValues) {
                    /* @var $this Grid\Filter\In */
                    if (!empty($selectedValues)) {
                        return Partner::query()->whereKey($selectedValues)->pluck('title', 'id')->toArray();
                    }
                    return [];
                })
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::PARTNERS->value]))
            ;
            $filter->between('created_at', \__('admin.created_at'))->datetime();
            $filter
                ->equal('visible_status', \__('admin.models.visible_status'))
                ->select([
                    '1' => \__('admin.switch_visible_statuses.on.text'),
                    '0' => \__('admin.switch_visible_statuses.off.text'),
                ])
            ;
        });

        $grid->column('visible_status', \__('admin.models.visible_status'))
            ->switch(\__('admin.switch_grid_visible_statuses'))
            ->sortable()
        ;
        $grid->column('title', __('admin.models.competition.title'))->editable();
        $grid->column('slug', __('admin.models.slug'));
        $grid
            ->column('short_content', __('admin.models.competition.short_content'))
            ->display(fn($data) => $data
                ? \Str::limit(\strip_tags($data), 250)
                : \__('admin.messages.empty_value')
            )
        ;
        $grid->column('expand', \__('admin.detail'))
            ->display(function (): string {
                return \__('admin.expand');
            })
            ->expand(ExpandCompetition::class)
        ;
        $grid->column('tile', \__('admin.models.competition.tile'))->image();

        return $grid;
    }

    protected function form(): Form
    {
        $form = new Form($this->model);

        \Admin::script($this->formScript());

        $form->disableCreatingCheck()->disableEditingCheck()->disableViewCheck();
        $form->builder()->getFooter()->disableReset();

        $id = $form->isEditing()
            ? (int)\Route::current()->parameter('competition')
            : null;
        $model = null !== $id
            ? $this->model::query()->with([
                'ageGroupsAll',
                'themes',
                'leadsAll',
                'prizes',
                'prizeInfo',
                'masterClassesAll',
                'partnersAll',
            ])->findOrFail($id)
            : new ($this->model);

        # add fields for processing
        # they rendered by custom views
        $form->text('titles_content->themes_enabled')->setDisplay(false);

        $form->text('titles_content->section_name->lead')->setDisplay(false);
        $form->text('titles_content->section_name->theme')->setDisplay(false);
        $form->text('titles_content->section_name->theme_block')->setDisplay(false);
        $form->text('titles_content->section_name->partner')->setDisplay(false);
        $form->text('titles_content->section_name->master-class')->setDisplay(false);

        $form->switch('titles_content->section_enabled->lead')->setDisplay(false);
        $form->switch('titles_content->section_enabled->theme')->setDisplay(false);
        $form->switch('titles_content->section_enabled->partner')->setDisplay(false);
        $form->switch('titles_content->section_enabled->master-class')->setDisplay(false);

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

                $form->row(function (Form\Row $form) {
                    $form->width(4);
                    /* @var $form Form */

                    $form
                        ->text('title', \__('admin.models.competition.title'))
                        ->rules(['string', 'max:255'])
                        ->required()
                    ;
                    $form
                        ->text('slug', \__('admin.models.slug'))
                        ->rules(['nullable', 'string', 'max:255'])
                    ;
                    $form
                        ->text('period', \__('admin.models.competition.period'))
                        ->rules(['nullable', 'string', 'max:255'])
                    ;
                });

                $form->row(fn(Form\Row|Form $form) => $form
                    ->multipleSelect('workTypesAll', \__('admin.models.competition.work_types.title'))
                    ->options(WorkType::query()->pluck('title', 'id')->toArray())
                    ->required()
                );

                $form->row(
                    fn(Form\Row|Form $form) => $form->divider(\__('admin.messages.tile_images'))
                );
                $form->row(function (Form\Row $form) {
                    $form->width(6);
                    /* @var $form Form */

                    $form
                        ->select('tile_size', \__('admin.models.competition.tile_size'))
                        ->options(TileSize::labels())
                        ->default(TileSize::SMALL->value)
                        ->required()
                    ;
                    $form
                        ->mediaLibrary('tile', \__('admin.models.competition.tile'))
                        ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
                        ->required()
                    ;
                });

                $form->row(
                    fn(Form\Row|Form $form) => $form->divider(\__('admin.messages.show_images'))
                );
                $form->row(function (Form\Row $form) {
                    /* @var $form Form */

                    $form
                        ->mediaLibrary('cover', \__('admin.models.competition.cover'))
                        ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
                        ->required()
                    ;
                });

                $form->row(function (Form\Row $form) {
                    $form->width(6);
                    /* @var $form Form */

                    $form
                        ->ckeditor('short_content', \__('admin.models.competition.short_content'))
                        ->rules(['string', 'max:' . (2 ** 31)])
                        ->required()
                    ;
                    $form
                        ->ckeditor('content', \__('admin.models.competition.content'))
                        ->rules(['nullable', 'string', 'max:' . (2 ** 31)])
                    ;
                });
            }
        );
        $form->tab(
            \__('admin.tabs.blocks-info'),
            function (Form $form) use ($model) {
                $fakeForm = new Form($this->model);

                $rowAgeGroups = new Form\Row(
                    fn(Form\Row|Form $form) => $form
                        ->list('ageGroups')
                        ->setView('admin.form.listfield.age-groups')
                        ->value($model->ageGroupsAll),
                    $fakeForm
                );
                $rowThemes = new Form\Row(
                    fn(Form\Row|Form $form) => $form
                        ->list('themes')
                        ->setView('admin.form.listfield.themes')
                        ->addVariables(['titles_content' => $model->titles_content->toArray()])
                        ->value($model->themes),
                    $fakeForm
                );
                $rowLeads = new Form\Row(
                    fn(Form\Row|Form $form) => $form
                        ->list('leads')
                        ->setView('admin.form.listfield.leads')
                        ->addVariables(['titles_content' => $model->titles_content->toArray()])
                        ->value($model->leadsAll),
                    $fakeForm
                );
                $rowPrizes = new Form\Row(
                    fn(Form\Row|Form $form) => $form
                        ->list('prizes')
                        ->setView('admin.form.listfield.prizes')
                        ->value($model->prizes)
                        ->addVariables(['prizeInfo' => $model->prizeInfo]),
                    $fakeForm
                );
                $rowMasterClasses = new Form\Row(
                    fn(Form\Row|Form $form) => $form
                        ->list('masterClasses')
                        ->setView('admin.form.listfield.master-classes')
                        ->addVariables([
                            'titles_content' => $model->titles_content->toArray(),
                            'themes' => $model->themes->map(fn(Theme $theme): array => [
                                'id' => $theme->id,
                                'text' => $theme->title,
                            ])->all(),
                        ])
                        ->value($model->masterClassesAll),
                    $fakeForm
                );
                $rowPartners = new Form\Row(
                    fn(Form\Row|Form $form) => $form
                        ->list('partners')
                        ->setView('admin.form.listfield.partners')
                        ->addVariables(['titles_content' => $model->titles_content->toArray()])
                        ->value($model->partnersAll),
                    $fakeForm
                );

                $collapse = new Collapse();
                $collapse->add(\__('admin.models.competition.age_groups.title'), $rowAgeGroups->render());
                $collapse->add(\__('admin.models.competition.themes.title'), $rowThemes->render());
                $collapse->add(\__('admin.models.competition.leads.title'), $rowLeads->render());
                $collapse->add(\__('admin.models.competition.prizes.title'), $rowPrizes->render());
                $collapse->add(\__('admin.models.competition.master_classes.title'), $rowMasterClasses->render());
                $collapse->add(\__('admin.models.competition.partners.title'), $rowPartners->render());

                $collapseBlock = $form
                    ->html($collapse->render())
                    ->setWidth(12, 0)
                    ->setLabelClass(['hidden'], true)
                ;
                $collapseBlock->validator(function ($input) {
                    return \validator(
                        $input ?: [],
                        [
                            'titles_content.section_name.*' => ['nullable', 'string', 'max:255'],
                            'ageGroups' => ['nullable', 'array'],
                            'themes' => ['nullable', 'array'],
                            'leads' => ['nullable', 'array'],
                            'prizes' => ['nullable', 'array'],
                            'prizes.*.title' => ['required', 'string', 'max:255'],
                            'prizes.*.description' => ['nullable', 'string', 'max:' . (2 ** 31)],
                            'prizeInfo.titles_content.like_text' => ['nullable', 'string', 'max:255'],
                            'prizeInfo.titles_content.gift_text' => ['nullable', 'string', 'max:255'],
                            'partners' => ['nullable', 'array'],
                            'partners.*.partner_text' => ['nullable', 'string', 'max:255'],
                        ]
                    );
                });
            }
        );
        $form->tab(
            \__('admin.tabs.display-settings-info'),
            function (Form $form) {
                $form->row(function (Form\Row $form) {
                    /* @var $form Form */

                    $form
                        ->text('titles_content->like_text', \__('admin.models.competition.pivot.like_text'))
                        ->rules(['nullable', 'string', 'max:255'])
                    ;
                });
                $form->row(function (Form\Row $form) {
                    $form->width(6);
                    /* @var $form Form */

                    $form
                        ->switch('titles_content->add_work_enabled', \__('admin.models.competition.pivot.add_work_enabled'))
                        ->states(\__('admin.switch_grid_visible_statuses'))
                    ;
                    $form
                        ->switch('titles_content->works_enabled', \__('admin.models.competition.pivot.works_enabled'))
                        ->states(\__('admin.switch_grid_visible_statuses'))
                    ;
                    $form
                        ->text('titles_content->add_work_text', \__('admin.models.competition.pivot.add_work_text'))
                        ->rules(['nullable', 'string', 'max:255'])
                    ;
                    $form
                        ->switch('titles_content->works_filtration_enabled', \__('admin.models.competition.pivot.works_filtration_enabled'))
                        ->states(\__('admin.switch_grid_visible_statuses'))
                    ;
                });
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

        $form->ignore([
            'sharing',
            'ageGroups',
            'themes',
            'leads',
            'prizes',
            'partners',
        ]);

        $form->saving(function (Form $form) {
            $validator = \validator([
                'masterClasses' => $form->input('masterClasses'),
            ], [
                'masterClasses' => ['nullable', 'array'],
                'masterClasses.titles_content' => ['nullable', 'array'],
                'masterClasses.titles_content.*' => ['required', 'string', 'max:255'],
                'masterClasses.main_id' => [
                    'nullable', 'integer', 'min:1', 'in_array:masterClasses.items.*.id',
                ],
                'masterClasses.items' => ['nullable', 'array'],
                'masterClasses.items.*.order_column' => ['nullable', 'integer', 'min:0'],
                'masterClasses.items.*.theme_ids' => ['nullable', 'array'],
                'masterClasses.items.*.theme_ids.*' => [
                    'required', 'integer', 'min:1', Rule::exists(Theme::class, 'id'),
                ],
                'masterClasses.items.*.id' => [
                    'required', 'integer', 'min:1', 'distinct', Rule::exists(MasterClass::class, 'id'),
                ],
            ]);

            if ($validator->fails()) {
                \admin_error('Optional blocks errors: ' . $validator->errors()->toJson(
                        \JSON_UNESCAPED_UNICODE
                    ));

                return \back()->withInput()->withErrors($validator);
            }

            return null;
        });

        $form->saved(function (Form $form) {
            if (1 === \count(\Arr::except(\request()->all(), ['_token', '_method']))) {
                return true;
            }

            $request = \request();
            $shareData = \array_filter(\request('sharing', []));

            if (false === empty($shareData)) {
                /* @var $model Competition */
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

            /* @var $model Competition */
            $model = $form->model();

            $data = $request->get('ageGroups');
            $form->isEditing() && $model->ageGroupsAll()->detach(touch: false);
            if (false === empty($data)) {
                foreach ($data as $val) {
                    if (isset($val['id'])) {
                        $model
                            ->ageGroupsAll()
                            ->attach(
                                $val['id'],
                                ['visible_status' => (bool)($val['visible_status'] ?? null)],
                                false
                            )
                        ;
                    }
                }
            }

            $data = $request->get('themes');
            $form->isEditing() && $model->themes()->detach(touch: false);
            if (false === empty($data)) {
                $titles = \array_filter($data['titles_content'] ?? []) ?: null;
                foreach ($data as $val) {
                    if (isset($val['id'])) {
                        $model
                            ->themes()
                            ->attach(
                                $val['id'],
                                \array_filter([
                                    'order_column' => (int)$val['order_column'] ?? null,
                                    'titles_content' => $titles ? \json_encode($titles) : "[]",
                                ]),
                                false
                            )
                        ;
                    }
                }
            }

            $data = $request->get('leads');
            $form->isEditing() && $model->leadsAll()->detach(touch: false);
            if (false === empty($data)) {
                $titles = \array_filter($data['titles_content'] ?? []) ?: null;
                foreach ($data as $val) {
                    if (isset($val['id'])) {
                        $model
                            ->leadsAll()
                            ->attach(
                                $val['id'],
                                \array_filter([
                                    'order_column' => (int)($val['order_column'] ?? null),
                                    'titles_content' => $titles ? \json_encode($titles) : "[]",
                                ]),
                                false
                            )
                        ;
                    }
                }
            }

            $data = $request->get('prizeInfo', []);
            $titles = \array_filter($data['titles_content'] ?? []);
            if (false === empty($data)) {
                $model
                    ->prizeInfo()
                    ->updateOrCreate(
                        ['competition_id' => $model->getKey()],
                        ['titles_content' => $titles]
                    )
                ;
            } else {
                $model->prizeInfo()->delete();
            }

            $data = \request('prizes', []);
            if (false === empty($data)) {
                $existIds = [];
                foreach ($data as $key => $val) {
                    $image = $val['image'] ?? null;
                    unset($val['image']);
                    if (\is_int($key)) {
                        $prize = $model->prizes()->find($key);
                        $prize?->update($val);
                    } else {
                        $prize = $model->prizes()->create($val);
                    }
                    if (isset($prize)) {
                        $existIds[] = $prize->getKey();
                        if (isset($image)) {
                            $prize->addMedia($image)->toMediaCollection(Prize::IMAGE_COLLECTION);
                        }
                    }
                }
                if (false === empty($existIds)) {
                    $model->prizes()->whereKeyNot($existIds)->delete();
                }
            } else {
                $model->prizes()->delete();
            }

            $masterClassesData = $form->input('masterClasses');

            if ($form->isEditing() && false === empty($masterClassesData)) {
                $titleContent = \array_filter($masterClassesData['titles_content'] ?? []);
                $mainMasterClassId = $masterClassesData['main_id'] ?? null;
                $mainMasterClassId = null !== $mainMasterClassId ? (int)$mainMasterClassId : $mainMasterClassId;

                $masterClassesUpdateData = [];

                foreach ($masterClassesData['items'] ?? [] as $item) {
                    $masterClassesUpdateData[(int)$item['id']] = [
                        'order_column' => (int)$item['order_column'],
                        'titles_content' => $titleContent,
                        'theme_ids' => \collect($item['theme_ids'] ?? [])
                            ->unique()
                            ->values()
                            ->map(fn($themeId): int => (int)$themeId)
                            ->all()
                        ,
                        'is_main' => (int)$item['id'] === $mainMasterClassId,
                    ];
                }

                $model->masterClassesAll()->sync($masterClassesUpdateData);
            }

            $data = $request->get('partners');
            $form->isEditing() && $model->partnersAll()->detach(touch: false);
            if (false === empty($data)) {
                $titles = \array_filter($data['titles_content'] ?? []) ?: [];
                $mainId = (int)($data['is_main'] ?? null);
                foreach ($data as $val) {
                    if (isset($val['id'])) {
                        $partnerTitles = \array_filter(\array_merge(
                            \array_filter(['partner_text' => $val['partner_text'] ?? []]),
                            $titles
                        ));
                        $model
                            ->partnersAll()
                            ->attach(
                                $val['id'],
                                \array_filter([
                                    'order_column' => (int)($val['order_column'] ?? null),
                                    'titles_content' => $partnerTitles ? \json_encode($partnerTitles) : "[]",
                                    'is_main' => ((int)$val['id']) === $mainId,
                                ]),
                                false
                            )
                        ;
                    }
                }
            }
        });

        return $form;
    }

    private function formScript(): string
    {
        return <<<JS
            $.fn.uncheckableRadio = function() {
                return this.each(function() {
                    $(this).mousedown(function() {
                        $(this).data('wasChecked', this.checked);
                    });
                    $(this).click(function() {
                        if ($(this).data('wasChecked'))
                            this.checked = false;
                    });
                });
            };
            window.refreshOrdering = function() {
                $(document)
                    .find('table.table tbody')
                    .each(function() {
                        $(this).find('tr.cell .grid-sortable-handle').each(function () {
                            let ordering = $(this).parents('td').find("input [name*='order_column']");
                            let pos = $(this).parents('tr.cell').first().index() + 1;
                            ordering.val(pos)
                        });
                    })
            }
            
            $(document).ready(function() {
                window.refreshOrdering();
                
                $(document).on('click', 'form[pjax-container] button[type="submit"]', function() {
                    let invalidFields = $('input:invalid, select:invalid, textarea:invalid', $(this).parents('form'));
                    
                    if (invalidFields.length) {
                        var activeTab = $('.nav-tabs .active a');
                        var activeAccordion = $('.panel-collapse.collapse.in');
                        
                        invalidFields.each(function() {
                            var fieldTab = $(this).closest('.tab-pane');
                            var fieldAccordion = $(this).closest('.panel-collapse');
                            if (activeTab.length > 0 && fieldTab.attr('id') !== activeTab.attr('href').substring(1)) {
                                $('a[href="#'+fieldTab.attr('id')+'"]').trigger('click')
                            }
                            
                            if (fieldAccordion.length > 0) {
                                if (activeAccordion.length) {
                                    if (fieldAccordion.attr('id') !== activeAccordion.attr('id')) {
                                        activeAccordion
                                            .one('hide.bs.collapse', function() {
                                                fieldAccordion.collapse('show')
                                            })
                                            .collapse('hide');
                                    }
                                } else {
                                    fieldAccordion.collapse('show');
                                }
                            }
                            this.checkValidity()    
                            return false;
                        });
                    }                     
                });
            });
        JS;
    }

    protected function detail($id): Show
    {
        $model = $this->model::query()->with([
            'ageGroupsAll',
            'themes',
            'leadsAll',
            'prizes',
            'prizeInfo',
            'masterClassesAll',
            'partnersAll',
            'workTypesAll',
            'sharing',
        ])->findOrFail($id);

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
        $show->field('title', \__('admin.models.competition.title'));
        $show->field('order_column', \__('admin.models.order_column'));
        $show
            ->field('workTypesAll', \__('admin.models.competition.work_types.title'))
            ->as(function () {
                /* @var $this Competition */
                return $this->workTypes->pluck('title')->implode('<br>');
            })
            ->unescape()
        ;
        $show->divider();
        $show->field('period', \__('admin.models.competition.period'));
        $show->field('short_content', \__('admin.models.competition.short_content'))->unescape();
        $show->field('content', \__('admin.models.competition.content'))->unescape();
        $show->field('cover', \__('admin.models.competition.cover'))->image();
        $show->field('tile', \__('admin.models.competition.tile'))->image();
        $show->divider();
        $show->field('created_at', \__('admin.created_at'));
        $show->field('updated_at', \__('admin.updated_at'));
        $show->divider();
        $show->field('sharing.title', \__('admin.models.sharing.title'));
        $show->field('sharing.description', \__('admin.models.sharing.description'))->unescape();
        $show->field('sharing.image', \__('admin.models.sharing.image'))->image();
        $show->divider();

        $show->relation('ageGroupsAll', \__('admin.models.competition.age_groups.title'), static function (Grid $grid) use ($model): void {
            $ageGroups = $model->ageGroupsAll->keyBy('id');
            $grid
                ->disableTools()
                ->disableActions()
                ->disableBatchActions()
                ->disableColumnSelector()
                ->disableCreateButton()
                ->disableExport()
            ;

            $grid
                ->column('_pivot_visible_status', \__('admin.models.competition.age_groups.pivot.visible_status'))
                ->display(function () use ($ageGroups) {
                    /* @var $this AgeGroup */
                    return ($ageGroups->get($this->getKey()) ?? $this)->_pivot_visible_status
                        ? \__('admin.switch_visible_statuses.on.text')
                        : \__('admin.switch_visible_statuses.off.text');
                })
            ;
            $grid->column('title', \__('admin.models.age_group.title'));
            $grid->column('min_age', \__('admin.models.age_group.min_age'));
            $grid->column('max_age', \__('admin.models.age_group.max_age'));
        });

        $show->relation('themes', \__('admin.models.competition.themes.title'), static function (Grid $grid) use ($model): void {
            $themes = $model->themes->keyBy('id');
            $grid
                ->disableTools()
                ->disableActions()
                ->disableBatchActions()
                ->disableColumnSelector()
                ->disableCreateButton()
                ->disableExport()
            ;

            $grid
                ->column('_pivot_order_column', \__('admin.models.order_column'))
                ->display(function () use ($themes) {
                    /* @var $this Theme */
                    return ($themes->get($this->getKey()) ?? $this)->_pivot_order_column;
                })
            ;
            $grid->column('title', \__('admin.models.theme.title'));
            $grid
                ->column('description', \__('admin.models.theme.description'))
                ->display(fn($data) => $data
                    ? \Str::limit(\strip_tags($data), 250)
                    : \__('admin.messages.empty_value')
                )
            ;
            $grid->column('tile', \__('admin.models.theme.tile'))->image();
        });

        $show->relation('leadsAll', \__('admin.models.competition.leads.title'), static function (Grid $grid) use ($model): void {
            $leads = $model->leadsAll->keyBy('id');
            $grid
                ->disableTools()
                ->disableActions()
                ->disableBatchActions()
                ->disableColumnSelector()
                ->disableCreateButton()
                ->disableExport()
            ;

            $grid
                ->column('_pivot_order_column', \__('admin.models.order_column'))
                ->display(function () use ($leads) {
                    /* @var $this Lead */
                    return ($leads->get($this->getKey()) ?? $this)->_pivot_order_column;
                })
            ;
            $grid
                ->column('visible_status', \__('admin.models.visible_status'))
                ->display(function ($value) {
                    return $value
                        ? \__('admin.switch_visible_statuses.on.text')
                        : \__('admin.switch_visible_statuses.off.text');
                })
            ;
            $grid->column('name', \__('admin.models.lead.name'));
            $grid
                ->column('description', \__('admin.models.lead.description'))
                ->display(fn($data) => $data
                    ? \Str::limit(\strip_tags($data), 250)
                    : \__('admin.messages.empty_value')
                )
            ;
            $grid->column('photo', \__('admin.models.lead.photo'))->image();
        });

        $show->relation('prizeInfo', \__('admin.models.competition.prizes_info.title'), static function (Show $show): void {
            $show->panel()->tools(function (Show\Tools $tools) {
                $tools->disableList()->disableEdit()->disableDelete();
            });
            $show
                ->field('gift', \__('admin.models.competition.prizes_info.pivot.titles_content.gift'))
                ->as(function () {
                    /* @var $this \App\Models\Competition\PrizeInfo */
                    return ($this->titles_content ?? [])['gift_text'] ?? \__('admin.messages.empty_value');
                })
            ;
            $show
                ->field('like', \__('admin.models.competition.prizes_info.pivot.titles_content.like'))
                ->as(function () {
                    /* @var $this \App\Models\Competition\PrizeInfo */
                    return ($this->titles_content ?? [])['like_text'] ?? \__('admin.messages.empty_value');
                })
            ;
        });

        $show->relation('prizes', \__('admin.models.competition.prizes.title'), static function (Grid $grid): void {
            $grid
                ->disableTools()
                ->disableActions()
                ->disableBatchActions()
                ->disableColumnSelector()
                ->disableCreateButton()
                ->disableExport()
            ;

            $grid->column('title', \__('admin.models.prizes.title'));
            $grid
                ->column('description', \__('admin.models.prizes.description'))
                ->display(fn($data) => $data
                    ? \Str::limit(\strip_tags($data), 250)
                    : \__('admin.messages.empty_value')
                )
            ;
            $grid->column('win_position', \__('admin.models.prizes.win_position'));
            $grid->column('link', \__('admin.models.prizes.link'))->link();
        });

        $show->relation('masterClassesAll', \__('admin.models.competition.master_classes.title'), static function (Grid $grid) use ($model): void {
            $masterClasses = $model->masterClassesAll->keyBy('id');
            $grid
                ->disableTools()
                ->disableActions()
                ->disableBatchActions()
                ->disableColumnSelector()
                ->disableCreateButton()
                ->disableExport()
            ;

            $grid
                ->column('_pivot_order_column', \__('admin.models.order_column'))
                ->display(function () use ($masterClasses) {
                    /* @var $this MasterClass */
                    return ($masterClasses->get($this->getKey()) ?? $this)->_pivot_order_column;
                })
            ;
            $grid
                ->column('_pivot_is_main', \__('admin.models.competition.partners.pivot.is_main'))
                ->display(function () use ($masterClasses) {
                    /* @var $this MasterClass */
                    return ($masterClasses->get($this->getKey()) ?? $this)->_pivot_is_main
                        ? \__('admin.switch_visible_statuses.on.text')
                        : \__('admin.switch_visible_statuses.off.text');
                })
            ;
            $grid->column('title', \__('admin.models.master_class.title'));
            $grid->column('video_link', \__('admin.models.master_class.video_link'))->link();
            $grid
                ->column('content', \__('admin.models.master_class.content'))
                ->display(fn($data) => $data
                    ? \Str::limit(\strip_tags($data), 250)
                    : \__('admin.messages.empty_value')
                )
            ;
            $grid->column('image', \__('admin.models.master_class.image'))->image();
        });

        $show->relation('partnersAll', \__('admin.models.competition.partners.title'), static function (Grid $grid) use ($model): void {
            $masterClasses = $model->partnersAll->keyBy('id');
            $grid
                ->disableTools()
                ->disableActions()
                ->disableBatchActions()
                ->disableColumnSelector()
                ->disableCreateButton()
                ->disableExport()
            ;

            $grid
                ->column('_pivot_order_column', \__('admin.models.order_column'))
                ->display(function () use ($masterClasses) {
                    /* @var $this Partner */
                    return ($masterClasses->get($this->getKey()) ?? $this)->_pivot_order_column;
                })
            ;
            $grid
                ->column('_pivot_is_main', \__('admin.models.competition.partners.pivot.is_main'))
                ->display(function () use ($masterClasses) {
                    /* @var $this Partner */
                    return ($masterClasses->get($this->getKey()) ?? $this)->_pivot_is_main
                        ? \__('admin.switch_visible_statuses.on.text')
                        : \__('admin.switch_visible_statuses.off.text');
                })
            ;
            $grid
                ->column('_pivot_partner_text', \__('admin.models.competition.partners.pivot.titles_content.partner_text'))
                ->display(function () use ($masterClasses) {
                    /* @var $this Partner */
                    $pivot = ($masterClasses->get($this->getKey()) ?? $this)->_pivot_titles_content;
                    if (false === empty($pivot) && isset($pivot['partner_text'])) {
                        return $pivot['partner_text'];
                    }

                    return \__('admin.messages.empty_value');
                })
            ;
            $grid
                ->column('visible_status', \__('admin.models.visible_status'))
                ->display(function ($value) {
                    return $value
                        ? \__('admin.switch_visible_statuses.on.text')
                        : \__('admin.switch_visible_statuses.off.text');
                })
            ;
            $grid->column('title', \__('admin.models.partner.title'));
            $grid->column('link', \__('admin.models.partner.link'))->link();
            $grid
                ->column('description', \__('admin.models.partner.description'))
                ->display(fn($data) => $data
                    ? \Str::limit(\strip_tags($data), 250)
                    : \__('admin.messages.empty_value')
                )
            ;
            $grid->column('logo', \__('admin.models.partner.logo'))->image();
        });

        return $show;
    }
}
