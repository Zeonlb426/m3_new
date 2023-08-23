<?php

namespace App\Admin\Controllers\Misc;

use App\Enums\Misc\ProcessingStatus;
use App\Models\Misc\Feedback;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;

class FeedbackController extends AdminController
{
    public function __construct(
        private readonly Feedback $model
    ) { $this->title = \__('admin.titles.feedbacks'); }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid($this->model);
        $grid->model()->with('user')->orderByDesc('created_at');

        $grid->disableExport();
        $grid->disableBatchActions()->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->actions(function(Grid\Displayers\Actions $actions) {
            $actions->disableEdit()->disableView();
        });

        $grid->filter(function(Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->ilike('name', \__('admin.models.feedback.name'));
            $filter->ilike('email', \__('admin.models.feedback.email'));
            $filter->equal('processing_status', \__('admin.models.feedback.processing_status'))->select(ProcessingStatus::labels());
        });

        $grid->column('processing_status', \__('admin.models.feedback.processing_status'))->sortable()->select(ProcessingStatus::labels());
        $grid->column('name', \__('admin.models.feedback.name'));
        $grid->column('email', \__('admin.models.feedback.email'));
        $grid->column('content', \__('admin.models.feedback.content'))
            ->style('display:block;max-width:800px;max-height:300px;overflow:auto;word-wrap:break-word;word-break:normal;');
        $grid
            ->column('user.name', \__('admin.models.feedback.user'))
            ->display(function() {
                /* @var $this Feedback */

                if (empty($this->user_id)) {
                    return '-';
                }

                return \sprintf(
                    '<a href="%s" target="_blank">%s (#%s)</a>',
                    \sprintf('%s?id=%d', \route(\admin_get_route('users.index')), $this->user->getKey()),
                    $this->user->name,
                    $this->user->getKey(),
                );
            })
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

        $form
            ->select('processing_status')
            ->options(ProcessingStatus::labels())
        ;

        return $form;
    }
}
