<?php /** @noinspection PhpUndefinedMethodInspection */

declare(strict_types = 1);

namespace App\Admin\Controllers;

use App\Models\Lead;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Validation\Rule;

class LeadController extends AdminController
{
    public function __construct(
        private readonly Lead $model
    )
    { $this->title = \__('admin.titles.leads'); }

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

        $grid->filter(function(Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->ilike('name', \__('admin.models.lead.name'));
            $filter->ilike('short_description', \__('admin.models.lead.short_description'));
            $filter->ilike('description', \__('admin.models.lead.description'));
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
        $grid->column('name', \__('admin.models.lead.name'))->editable();
        $grid->column('slug', \__('admin.models.slug'));
        $grid
            ->column('description', \__('admin.models.lead.description'))
            ->display(fn($data) => $data
                ? \Str::limit(\strip_tags($data), 250)
                : \__('admin.messages.empty_value')
            )
        ;
        $grid->column('photo', \__('admin.models.lead.photo'))->image();

        return $grid;
    }

    protected function form(): Form
    {
        $form = new Form($this->model);

        $form->disableCreatingCheck()->disableEditingCheck()->disableViewCheck();
        $form->builder()->getFooter()->disableReset();

        $id = $form->isEditing()
            ? (int) \Route::current()->parameter('lead')
            : null
        ;

        $form->row(function(Form\Row $form) {
            $form->width(4);
            /* @var $form Form */

            $form
                ->switch('visible_status', \__('admin.models.visible_status'))
                ->states(\__('admin.switch_visible_statuses'))
                ->default(true)
            ;
            $form->number('order_column', \__('admin.models.order_column'));
        });

        $form->row(function(Form\Row $form) use ($id) {
            $form->width(6);
            /* @var $form Form */

            $form
                ->text('name', \__('admin.models.lead.name'))
                ->rules(['string', 'max:255'])
                ->required()
            ;
            $form
                ->text('slug', \__('admin.models.slug'))
                ->creationRules(['nullable', 'string', 'max:255', Rule::unique($this->model->getTable(), 'slug')])
                ->updateRules(['nullable', 'string', 'max:255', Rule::unique($this->model->getTable(), 'slug')->ignore($id)])
            ;
        });
        $form->row(function(Form\Row $form) {
            $form->width(6);
            /* @var $form Form */

            $form
                ->ckeditor('description', \__('admin.models.lead.description'))
                ->rules(['string', 'max:' . (2**31)])->required()
            ;
            $form
                ->ckeditor('short_description', \__('admin.models.lead.short_description'))
                ->rules(['nullable','string', 'max:' . (2**16)])
            ;
        });
        $form->row(function(Form\Row $form) {
            /* @var $form Form */

            $form
                ->mediaLibrary('photo', \__('admin.models.lead.photo'))
                ->removable()
                ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
            ;
        });

        if ($form->isEditing()) {
            $form->row(function (Form\Row $form) {
                $form->width(6);
                /* @var $form Form */

                $form->text('created_at', \__('admin.created_at'))->disable();
                $form->text('updated_at', \__('admin.updated_at'))->disable();
            });
        }

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
        $show->field('name', \__('admin.models.lead.name'));
        $show->field('slug', \__('admin.models.slug'));
        $show->field('order_column', \__('admin.models.order_column'));
        $show->divider();
        $show->field('short_description', \__('admin.models.lead.short_description'))->unescape();
        $show->field('description', \__('admin.models.lead.description'))->unescape();
        $show->field('photo', \__('admin.models.lead.photo'))->image();
        $show->divider();
        $show->field('created_at', \__('admin.created_at'));
        $show->field('updated_at', \__('admin.updated_at'));

        return $show;
    }
}
