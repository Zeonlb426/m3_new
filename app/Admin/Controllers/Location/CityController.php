<?php

declare(strict_types = 1);

namespace App\Admin\Controllers\Location;

use App\Admin\Controllers\Api\DropDownListLoaderController;
use App\Admin\Selectable\RegionSelectable;
use App\Enums\LoaderType;
use App\Models\Location\City;
use App\Models\Location\Region;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

final class CityController extends AdminController
{
    public function __construct(
        private readonly City $model
    ) { $this->title = \__('admin.titles.cities'); }

    public function grid(): Grid
    {
        $grid = new Grid($this->model);

        $grid->model()->with('region');

        $grid->paginate(50);

        $grid->disableExport();
        $grid->actions(function (Grid\Displayers\DropdownActions $actions) {
            $actions->disableView();
            $actions->disableDelete();
        });

        $grid->disableBatchActions()->disableColumnSelector()->disableCreateButton();

        $grid->filter(function(Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->ilike('title', \__('admin.models.city.title'));
            $filter
                ->in('region_id', \__('admin.models.city.region'))
                ->multipleSelect(function($selectedValues) {
                     /* @var $this Grid\Filter\In */
                    if (!empty($selectedValues)) {
                        return Region::query()->whereKey($selectedValues)->pluck('title', 'id')->toArray();
                    }
                    return [];
                })
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::REGION->value]))
            ;
        });

        $grid->column('title', \__('admin.models.city.title'))->sortable();
        $grid->column('region.title', \__('admin.models.region.title'))->sortable();

        return $grid;
    }

    public function form(): Form
    {
        $form = new Form($this->model);

        $form->disableCreatingCheck()->disableEditingCheck()->disableViewCheck();
        $form->builder()->getFooter()->disableReset();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        $form->text('title', \__('admin.models.city.title'))
            ->rules(['string', 'max:255'])
        ;
        $form->belongsTo('region_id', RegionSelectable::class, \__('admin.models.city.region'));

        return $form;
    }

    public function detail($id): Show
    {
        $show = new Show($this->model::query()->with('region')->findOrFail($id));

        $show->field('id', \__('admin.models.id'));
        $show->field('title', \__('admin.models.city.title'));
        $show->relation('region', \__('admin.models.region.cities'), static function(Show $show): void {
            $show->panel()->tools(function(Show\Tools $tools) {
                $tools->disableList()->disableEdit()->disableDelete();
            });
            $show->field('id', \__('admin.models.id'));
            $show->field('title', \__('admin.models.region.title'));
            $show->field('code', \__('admin.models.region.code'));
        });

        return $show;
    }
}
