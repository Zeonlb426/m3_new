<?php /** @noinspection PhpUndefinedMethodInspection */

namespace App\Admin\Controllers;

use App\Models\Slider;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SliderController extends AdminController
{
    public function __construct(
        private readonly Slider $model
    ) { $this->title = \__('admin.titles.sliders'); }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid($this->model);
        $grid->model()->orderBy('order_column');

        $grid->disableExport();
        $grid->disableBatchActions()->disableColumnSelector();

        $grid->sortable();

        $grid->filter(function(Grid\Filter $filter) {
            $filter->ilike('short_title', \__('admin.models.slider.short_title'));
            $filter->ilike('title', \__('admin.models.slider.title'));
            $filter->ilike('description', \__('admin.models.slider.description'));
            $filter->ilike('link', \__('admin.models.slider.link'));
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
        $grid->column('short_title', \__('admin.models.slider.short_title'))->editable();
        $grid->column('title', \__('admin.models.slider.title'))->editable();
        $grid->column('link', \__('admin.models.slider.link'))->link();
        $grid->column('image', \__('admin.models.slider.image'))->image();

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

        $form->disableCreatingCheck()->disableEditingCheck()->disableViewCheck();
        $form->builder()->getFooter()->disableReset();

        $form->row(function(Form\Row $form) {
            $form->width(4);
            /* @var $form Form */

            $form
                ->switch('visible_status', \__('admin.models.visible_status'))
                ->states(\__('admin.switch_visible_statuses'))
                ->default(true)
            ;
            $form->number('order_column', \__('admin.models.visible_status'));
        });
        $form->row(function(Form\Row $form) {
            $form->width(6);
            /* @var $form Form */

            $form
                ->text('short_title', \__('admin.models.slider.short_title'))
                ->rules(['nullable', 'string', 'max:255'])
            ;
            $form
                ->text('title', \__('admin.models.slider.title'))
                ->rules(['nullable', 'string', 'max:255'])
            ;
        });
        $form->row(function(Form\Row $form) {
            /* @var $form Form */

            $form
                ->url('link', \__('admin.models.slider.link'))
                ->rules(['nullable', 'url', 'max:255'])
            ;
            $form
                ->ckeditor('description', \__('admin.models.slider.description'))
                ->rules(['nullable', 'string', 'max:' . (2**31)])
            ;
        });
        $form->row(function(Form\Row $form) {
            $form->width(6);
            /* @var $form Form */
            $form
                ->mediaLibrary('image', \__('admin.models.slider.image'))
                ->rules(['image', 'max:' . (5 * 1024)])
                ->required()
            ;
            $form
                ->mediaLibrary('image_mobile', \__('admin.models.slider.image_mobile'))
                ->rules(['image', 'max:' . (5 * 1024)])
                ->required()
            ;
        });


        return $form;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail(string|int $id)
    {
        $show = new Show($this->model::query()->findOrFail($id));

        $show->field('id', \__('admin.models.id'));
        $show
            ->field('visible_status', \__('admin.models.visible_status'))
            ->as(fn($status) => $status
                ? \__('admin.switch_visible_statuses.on.text')
                : \__('admin.switch_visible_statuses.off.text')
            )
        ;
        $show->divider();
        $show->field('short_title', \__('admin.models.slider.short_title'));
        $show->field('title', \__('admin.models.slider.title'));
        $show->field('link', \__('admin.models.slider.link'))->link();
        $show->field('order_column', \__('admin.models.order_column'));
        $show->divider();
        $show->field('description', \__('admin.models.slider.description'))->unescape();
        $show->field('image', \__('admin.models.slider.image'))->image();
        $show->field('image_mobile', \__('admin.models.slider.image_mobile'))->image();

        return $show;
    }
}
