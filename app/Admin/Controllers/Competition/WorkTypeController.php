<?php

declare(strict_types = 1);

namespace App\Admin\Controllers\Competition;

use App\Models\Competition\WorkType;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;

class WorkTypeController extends AdminController
{
    const AVAILABLE_FORMATS = [
        'aif', 'mid', 'midi', 'mp3', 'mpa', 'ogg', 'wav', 'wma',
        '3gp', 'avi', 'flv', 'm4v', 'mkv', 'mov', 'mp4', 'mpg', 'mpeg', 'rm', 'webm', 'wmv',
        'ods', 'xls', 'xlsx', 'doc', 'odt', 'pdf', 'rtf', 'tex', 'txt',
        'ai', 'bmp', 'gif', 'ico', 'jpeg', 'jpg', 'png', 'tif', 'tiff', 'webp', 'psd',
    ];

    public function __construct(
        private readonly WorkType $model
    ) { $this->title = \__('admin.titles.work-types'); }

    public function grid(): Grid
    {
        $grid = new Grid($this->model);

        $grid->disableExport()->disableActions();
        $grid->disableBatchActions()->disableColumnSelector()->disableCreateButton();

        $grid->filter(function(Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->ilike('title', \__('admin.name'));
            $filter->ilike('slug', \__('admin.models.slug'));
        });

        $grid->column('visible_status', \__('admin.models.visible_status'))
            ->switch(\__('admin.switch_grid_visible_statuses'))
            ->sortable()
        ;
        $grid
            ->column('formats', \__('admin.models.competition.work_types.formats'))
            ->multipleSelect($this->formatsSelect())
        ;
        $grid->column('title', \__('admin.name'));
        $grid->column('slug', \__('admin.models.slug'));

        return $grid;
    }


    public function form(): Form
    {
        $form = new Form($this->model);

        $form
            ->switch('visible_status', \__('admin.models.visible_status'))
            ->states(\__('admin.switch_visible_statuses'))
            ->default(true)
        ;
        $form
            ->multipleSelect('formats')
            ->options($this->formatsSelect())
        ;

        return $form;
    }

    private function formatsSelect(): array
    {
        return \array_combine(self::AVAILABLE_FORMATS, self::AVAILABLE_FORMATS);
    }
}
