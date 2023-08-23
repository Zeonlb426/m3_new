<?php
/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

namespace App\Admin\Controllers\News;

use App\Models\News\News;
use App\Models\Objects\VkLink;
use App\Rules\VkLink as VkLinkValidator;
use App\Rules\YoutubeLink as YoutubeLinkValidator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class NewsController extends AdminController
{
    public function __construct(
        private readonly News $model
    ) {
        $this->title = \__('admin.titles.news');
    }

    public function destroy($id)
    {
        // отключаем возможность вызвать forceDelete из админки
        $news = News::query()
            ->withoutTrashed()
            ->findOrFail(Str::of($id)->explode(',')->collect()->filter(), ['id'])
        ;

        return parent::destroy($news->pluck('id')->join(','));
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
            ->withoutTrashed()
        ;

        $grid->disableExport();
        $grid->disableBatchActions()->disableColumnSelector();

        $grid->filter(function (Grid\Filter $filter) {
            $filter->ilike('title', \__('admin.models.news.title'));
            $filter->ilike('announce', \__('admin.models.news.announce'));
            $filter->ilike('content', \__('admin.models.news.content'));
            $filter->ilike('video_link', \__('admin.models.news.video_link'));
            $filter->between('publish_date', \__('admin.models.news.publish_date'))->datetime();
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

        $grid->column('publish_date', \__('admin.models.news.publish_date'))->date()->sortable();
        $grid->column('title', \__('admin.models.news.title'))->editable();
        $grid
            ->column('content', \__('admin.models.news.content'))
            ->display(fn($data) => $data
                ? \Str::limit(\strip_tags($data), 250)
                : \__('admin.messages.empty_value')
            )
        ;
        $grid->column('likes_total_count', \__('admin.models.news.likes'))->style('text-align:center')->sortable();
        $grid->column('cover', \__('admin.models.news.cover'))->image();

        return $grid;
    }

    protected function form(): Form
    {
        $form = new Form($this->model);

        $form->disableCreatingCheck()->disableEditingCheck()->disableViewCheck();
        $form->builder()->getFooter()->disableReset();

        $form->row(function (Form\Row $form) {
            $form->width(6);
            /* @var $form Form */

            $form->switch('visible_status', __('admin.models.visible_status'))->default(false)
                ->states(\__('admin.switch_visible_statuses'))
                ->default(true)
            ;
            $form->datetime('publish_date', \__('admin.models.news.publish_date'))
                ->required()
                ->options([
                    'format' => 'Y-m-d',
                    'minDate' => \now()->subCentury()->format('Y-m-d H:i:s'),
                    'maxDate' => \now()->addYear()->format('Y-m-d H:i:s'),
                ])
                ->rules([
                    \sprintf('after:%s', \now()->subCentury()->format('Y-m-d H:i:s')),
                    \sprintf('before:%s', \now()->addYear()->format('Y-m-d H:i:s')),
                ])
            ;

            $form->text('title', \__('admin.models.news.title'))
                ->rules(['string', 'max:255'])
                ->required()
            ;

            $form->url('video_link', \__('admin.models.news.video_link'))
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
            /* @var $form Form */
            $form->ckeditor('announce', \__('admin.models.news.announce'))
                ->rules(['string', 'max:' . (2 ** 31)])
            ;
            $form->ckeditor('content', \__('admin.models.news.content'))
                ->rules(['string', 'max:' . (2 ** 31)])
                ->required()
            ;
            $form->mediaLibrary('cover', \__('admin.models.news.cover'))
                ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
            ;
        });
        if ($form->isEditing()) {
            $form->row(function (Form\Row $form) {
                $form->width(4);
                /* @var $form Form */
                $form->text('likes_total_count', \__('admin.models.news.likes'))->disable();
                $form->text('created_at', \__('admin.created_at'))->disable();
                $form->text('updated_at', \__('admin.updated_at'))->disable();
            });
        }

        $form->ignore(['likes_total_count']);

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
        $model = $this->model::query()->findOrFail($id);
        $show = new Show($model);

        $show->field('id', \__('admin.models.id'));
        $show->field('visible_status', \__('admin.models.visible_status'))
            ->as(fn($status) => $status
                ? \__('admin.switch_visible_statuses.on.text')
                : \__('admin.switch_visible_statuses.off.text')
            )
        ;
        $show->divider();
        $show->field('title', \__('admin.models.news.title'));
        $show->field('video_link', \__('admin.models.news.video_link'))->link();
        $show->divider();
        $show->field('publish_date', \__('admin.models.news.publish_date'));
        $show->field('announce', \__('admin.models.news.announce'))->unescape();
        $show->field('content', \__('admin.models.news.content'))->unescape();
        $show->field('cover_thumb', \__('admin.models.news.cover'))->image();
        $show->divider();
        $show->field('likes_total_count', \__('admin.models.news.likes'));
        $show->field('created_at', \__('admin.created_at'));
        $show->field('updated_at', \__('admin.updated_at'));

        return $show;
    }
}
