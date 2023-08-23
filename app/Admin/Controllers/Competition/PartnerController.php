<?php /** @noinspection PhpUndefinedMethodInspection */

namespace App\Admin\Controllers\Competition;

use App\Models\Competition\Partner;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PartnerController extends AdminController
{
    public function __construct(
        private readonly Partner $model
    ) { $this->title = \__('admin.titles.partners'); }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid($this->model);

        $grid->disableExport();
        $grid->disableBatchActions()->disableColumnSelector();

        $grid->filter(function(Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->ilike('title', \__('admin.models.partner.title'));
            $filter->ilike('description', \__('admin.models.partner.description'));
            $filter->ilike('link', \__('admin.models.partner.link'));
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
        $grid->column('title', \__('admin.models.partner.title'))->editable()->sortable();
        $grid->column('link', \__('admin.models.partner.link'))->link();
        $grid->column('logo', \__('admin.models.partner.logo'))->image();
        $grid
            ->column('description', \__('admin.models.partner.description'))
            ->display(fn($data) => $data
                ? \Str::limit(\strip_tags($data), 250)
                : \__('admin.messages.empty_value')
            )
        ;

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

        $form
            ->switch('visible_status', \__('admin.models.visible_status'))
            ->states(\__('admin.switch_visible_statuses'))
            ->default(true)
        ;
        $form
            ->text('title', \__('admin.models.partner.title'))
            ->rules(['string', 'max:255'])
            ->required()
        ;
        $form
            ->url('link', \__('admin.models.partner.link'))
            ->rules(['url', 'max:255'])
            ->required()
        ;
        $form
            ->ckeditor('description', \__('admin.models.lead.description'))
            ->rules(['string', 'max:10000'])
            ->required()
        ;
        $form
            ->mediaLibrary('logo', \__('admin.models.partner.logo'))
            ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
        ;
        $form
            ->mediaLibrary('background', \__('admin.models.partner.background'))
            ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
        ;
        $form
            ->mediaLibrary('slider', \__('admin.models.partner.slider'))
            ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
        ;

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
        $show->field('title', \__('admin.models.partner.title'));
        $show->field('link', \__('admin.models.partner.link'))->link();
        $show->field('description', \__('admin.models.partner.description'))->unescape();
        $show->field('logo', \__('admin.models.partner.logo'))->image();
        $show->field('background', \__('admin.models.partner.background'))->image();
        $show->field('slider', \__('admin.models.partner.slider'))->image();

        return $show;
    }
}
