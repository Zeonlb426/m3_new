<?php /** @noinspection PhpUndefinedMethodInspection */

namespace App\Admin\Controllers\Misc;

use App\Models\Misc\Document;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Validation\Rule;

class DocumentController extends AdminController
{
    public function __construct(
        private readonly Document $model
    ) { $this->title = \__('admin.titles.documents'); }

    protected function grid(): Grid
    {
        $grid = new Grid($this->model);

        $grid->disableExport();
        $grid->disableBatchActions()->disableColumnSelector();

        $grid->filter(function(Grid\Filter $filter) {
            $filter->disableIdFilter();

            $filter->ilike('name', \__('admin.models.document.name'));
            $filter->ilike('file_name', \__('admin.models.document.file_name'));
            $filter->ilike('slug', \__('admin.models.slug'));
        });

        $grid->column('name', \__('admin.models.document.name'))->editable();
        $grid->column('file_name', \__('admin.models.document.file_name'))->editable();
        $grid->column('slug', \__('admin.models.slug'));
        $grid->column('file', \__('admin.models.document.file'))->link();

        return $grid;
    }

    protected function form(): Form
    {
        $form = new Form($this->model);

        $form->disableCreatingCheck()->disableEditingCheck()->disableViewCheck();
        $form->builder()->getFooter()->disableReset();

        $id = $form->isEditing()
            ? (int) \Route::current()->parameter('document')
            : null
        ;

        $form->text('name', \__('admin.models.document.name'))->required();
        $form->text('file_name', \__('admin.models.document.file_name'));
        $form
            ->text('slug', \__('admin.models.slug'))
            ->rules(['nullable', 'string', 'max:255'])
            ->creationRules([Rule::unique($this->model->getTable(), 'slug')])
            ->updateRules([Rule::unique($this->model->getTable(), 'slug')->ignore($id)])
        ;
        $form->mediaLibrary('file', \__('admin.models.document.file'))
            ->rules([
                'file', 'max:' . (5 * 1024),
                'mimes:pdf,doc,docx', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])
            ->required()
        ;

        return $form;
    }

    protected function detail($id): Show
    {
        $show = new Show($this->model::query()->findOrFail($id));

        $show->field('id', \__('admin.models.id'));
        $show->divider();
        $show->field('name', \__('admin.models.document.name'));
        $show->field('file_name', \__('admin.models.document.file_name'));
        $show->field('slug', \__('admin.models.slug'));
        $show->divider();
        $show->field('file', \__('admin.models.document.file'))->link();

        return $show;
    }
}
