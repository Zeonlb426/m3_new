<?php
/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

namespace App\Admin\Controllers\Promo;

use App\Models\Objects\VkLink;
use App\Models\Promo\SuccessHistory;
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

final class SuccessHistoryController extends AdminController
{
    public function __construct(
        private readonly SuccessHistory $model
    ) {
        $this->title = \__('admin.titles.success-histories');
    }

    public function destroy($id)
    {
        // отключаем возможность вызвать forceDelete из админки
        $successHistories = SuccessHistory::query()
            ->withoutTrashed()
            ->findOrFail(Str::of($id)->explode(',')->collect()->filter(), ['id'])
        ;

        return parent::destroy($successHistories->pluck('id')->join(','));
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
            ->orderBy('order_column')
            ->withoutTrashed()
        ;
        $grid->sortable();

        $grid->disableExport();
        $grid->disableBatchActions()->disableColumnSelector();

        $grid->filter(function (Grid\Filter $filter) {
            $filter->ilike('short_title', \__('admin.models.success_history.short_title'));
            $filter->ilike('title', \__('admin.models.success_history.title'));
            $filter->ilike('short_description', \__('admin.models.success_history.short_description'));
            $filter->ilike('description', \__('admin.models.success_history.description'));
            $filter->ilike('video_link', \__('admin.models.success_history.video_link'));
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
        $grid->column('short_title', \__('admin.models.success_history.short_title'))->editable();
        $grid->column('title', \__('admin.models.success_history.title'))->editable();
        $grid
            ->column('description', \__('admin.models.success_history.description'))
            ->display(fn($data) => $data
                ? \Str::limit(\strip_tags($data), 250)
                : \__('admin.messages.empty_value')
            )
        ;
        $grid->column('video_link', \__('admin.models.success_history.video_link'))->link();
        $grid->column('likes_total_count', \__('admin.models.success_history.likes'))->style('text-align:center')->sortable();
        $grid->column('image', \__('admin.models.success_history.image'))->image();

        return $grid;
    }

    protected function form(): Form
    {
        $form = new Form($this->model);

        $form->disableCreatingCheck()->disableEditingCheck()->disableViewCheck();
        $form->builder()->getFooter()->disableReset();

        $form->tab(
            \__('admin.tabs.main-info'),
            function (Form $form) {
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
                        ->text('short_title', \__('admin.models.success_history.short_title'))
                        ->rules(['string', 'max:255'])
                    ;
                    $form
                        ->text('title', \__('admin.models.success_history.title'))
                        ->rules(['string', 'max:255'])
                        ->required()
                    ;
                    $form
                        ->url('video_link', \__('admin.models.success_history.video_link'))
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
                            )
                        ])
                    ;
                });
                $form->row(function (Form\Row $form) {
                    $form->width(6);
                    /* @var $form Form */

                    $form
                        ->ckeditor('short_description', \__('admin.models.success_history.short_description'))
                        ->rules(['string', 'max:' . (2 ** 31)])
                        ->required()
                    ;
                    $form
                        ->ckeditor('description', \__('admin.models.success_history.description'))
                        ->rules(['string', 'max:' . (2 ** 31)])
                    ;
                });
                $form->row(function (Form\Row $form) {
                    /* @var $form Form */

                    $form
                        ->mediaLibrary('image', \__('admin.models.success_history.image'))
                        ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
                    ;
                });

                if ($form->isEditing()) {
                    $form->row(function (Form\Row $form) {
                        $form->width(4);
                        /* @var $form Form */

                        $form->text('likes_total_count', \__('admin.models.success_history.likes'))->disable();
                        $form->text('created_at', \__('admin.created_at'))->disable();
                        $form->text('updated_at', \__('admin.updated_at'))->disable();
                    });
                }
            },
            true
        );
        $form->tab(
            \__('admin.tabs.sharing-info'),
            function (Form $form) {
                $form
                    ->text('sharing.title', \__('admin.models.sharing.title'))
                    ->rules(['nullable', 'string', 'max:255'])
                ;
                $form
                    ->ckeditor('sharing.description', \__('admin.models.sharing.description'))
                    ->rules(['nullable', 'string', 'max:' . (2 ** 31)])
                ;
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
                /* @var $model SuccessHistory */
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
        $model = $this->model::query()->with('sharing')->findOrFail($id);
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
        $show->field('short_title', \__('admin.models.success_history.short_title'));
        $show->field('title', \__('admin.models.success_history.title'));
        $show->field('video_link', \__('admin.models.success_history.video_link'))->link();
        $show->field('order_column', \__('admin.models.order_column'));
        $show->divider();
        $show->field('short_description', \__('admin.models.success_history.short_description'))->unescape();
        $show->field('description', \__('admin.models.success_history.description'))->unescape();
        $show->field('image', \__('admin.models.success_history.image'))->image();
        $show->divider();
        $show->field('likes_total_count', \__('admin.models.success_history.likes'));
        $show->field('created_at', \__('admin.created_at'));
        $show->field('updated_at', \__('admin.updated_at'));
        $show->divider();
        $show->field('sharing.title', \__('admin.models.sharing.title'));
        $show->field('sharing.description', \__('admin.models.sharing.description'))->unescape();
        $show->field('sharing.image', \__('admin.models.sharing.image'))->image();

        return $show;
    }
}
