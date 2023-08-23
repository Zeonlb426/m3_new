<?php

declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Models\AgeGroup;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Validation\Rule;

class AgeGroupController extends AdminController
{
    public function __construct(
        private readonly AgeGroup $model
    ) { $this->title = \__('admin.titles.age-groups'); }

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
        $grid->actions(function(Grid\Displayers\Actions $actions) {
            $actions->disableView();
        });

        $grid->filter(function(Grid\Filter $filter) {
            $filter->ilike('title', \__('admin.models.age_group.title'));
            $filter->gt('min_age', \__('admin.models.age_group.min_age'));
            $filter->lt('max_age', \__('admin.models.age_group.max_age'));
        });

        $grid->quickCreate(function(Grid\Tools\QuickCreate $create) {
            $create
                ->text('title', \__('admin.models.age_group.title'))
                ->rules(['string', 'max:255'])
                ->required()
            ;
            $create->integer('min_age', \__('admin.models.age_group.min_age'))
                ->required()
                ->rules([
                    'required', 'integer', 'min:1', 'max:150', 'lt:max_age'
                ])
            ;
            $create->integer('max_age', \__('admin.models.age_group.max_age'))
                ->required()
                ->rules([
                    'required', 'integer', 'min:1', 'max:150', 'gt:min_age'
                ])
            ;
        });

        $grid->column('title', \__('admin.models.age_group.title'))->editable();
        $grid->column('slug', \__('admin.models.slug'))->editable()->sortable();
        $grid->column('min_age', \__('admin.models.age_group.min_age'))->sortable();
        $grid->column('max_age', \__('admin.models.age_group.max_age'))->sortable();

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

        $id = $form->isEditing()
            ? (int) \Route::current()->parameter('age_group')
            : null
        ;

        $form
            ->text('title', \__('admin.models.age_group.title'))
            ->rules(['string', 'max:255'])
            ->required()
        ;
        $form
            ->text('slug', \__('admin.models.slug'))
            ->creationRules(['nullable', 'string', 'max:255', Rule::unique($this->model->getTable(), 'slug')])
            ->updateRules(['nullable', 'string', 'max:255', Rule::unique($this->model->getTable(), 'slug')->ignore($id)])
        ;
        $form->number('min_age', \__('admin.models.age_group.min_age'))
            ->required()
            ->rules([
                'required', 'integer', 'min:1', 'max:150', 'lt:max_age'
            ])
        ;
        $form->number('max_age', \__('admin.models.age_group.max_age'))
            ->required()
            ->rules([
                'required', 'integer', 'min:1', 'max:150', 'gt:min_age'
            ])
        ;

        return $form;
    }
}
