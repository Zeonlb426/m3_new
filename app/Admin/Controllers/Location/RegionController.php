<?php

declare(strict_types = 1);

namespace App\Admin\Controllers\Location;

use App\Models\Location\Region;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Validation\Rule;

final class RegionController extends AdminController
{
    public function __construct(
        private readonly Region $model
    ) { $this->title = \__('admin.titles.regions'); }

    public function grid(): Grid
    {
        $grid = new Grid($this->model);

        $grid->paginate(50);

        $grid->disableExport();
        $grid->actions(function (Grid\Displayers\DropdownActions $actions) {
            $actions->disableView();
            $actions->disableDelete();
        });

        $grid->disableBatchActions()->disableColumnSelector()->disableCreateButton();

        $grid->filter(function(Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->ilike('title', \__('admin.models.region.title'));
            $filter->ilike('code', \__('admin.models.region.code'));
        });

        $grid->column('title', \__('admin.models.region.title'))->sortable();
        $grid->column('code', \__('admin.models.region.code'))->sortable();

        return $grid;
    }

    public function form(): Form
    {
        $id = (int) (\Route::current()->parameter('region')) ?: null;

        $form = new Form($this->model);

        $form->disableCreatingCheck()->disableEditingCheck()->disableViewCheck();
        $form->builder()->getFooter()->disableReset();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        $form->text('title', \__('admin.models.region.title'))
            ->rules(['string', 'max:255'])
        ;
        $form->text('code', \__('admin.models.region.code'))
            ->creationRules([Rule::unique($this->model->getTable(), 'code')])
            ->updateRules([Rule::unique($this->model->getTable(), 'code')->ignore($id)])
            ->rules(['string', 'max:5'])
        ;

        return $form;
    }

    public function detail($id): Show
    {
        $show = new Show($this->model::query()->with('cities')->findOrFail($id));

        $show->field('id', \__('admin.models.id'));
        $show->field('title', \__('admin.models.region.title'));
        $show->field('code', \__('admin.models.region.code'));
        $show->relation('cities', \__('admin.models.region.cities'), static function(Grid $grid): void {
            $grid
                ->disableTools()
                ->disableActions()
                ->disableBatchActions()
                ->disableColumnSelector()
                ->disableCreateButton()
                ->disableExport()
            ;

            $grid->column('id', \__('admin.models.id'));
            $grid->column('title', \__('admin.models.city.title'));
        });

        return $show;
    }
}
