<?php /** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

namespace App\Admin\Controllers\MasterClass;

use App\Admin\Controllers\Api\DropDownListLoaderController;
use App\Enums\LoaderType;
use App\Models\Lead;
use App\Models\MasterClass\Course;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class CourseController extends AdminController
{
    public function __construct(
        private readonly Course $model
    ) { $this->title = \__('admin.titles.courses'); }

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
            $filter->ilike('name', \__('admin.models.course.name'));
            $filter->ilike('description', \__('admin.models.course.description'));
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
        $grid->column('name', \__('admin.models.course.name'))->editable();
        $grid->column('slug', \__('admin.models.slug'));
        $grid
            ->column('description', \__('admin.models.course.description'))
            ->display(fn($data) => $data
                ? \Str::limit(\strip_tags($data), 250)
                : \__('admin.messages.empty_value')
            )
        ;

        $grid->column('created_at', \__('admin.created_at'))->default()->sortable();

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

        $id = $form->isEditing()
            ? (int) \Route::current()->parameter('course')
            : null
        ;

        $form->row(function(Form\Row $form) {
            $form->width(4);
            /* @var $form Form */

            $form->number('order_column', \__('admin.models.order_column'));
            $form
                ->switch('visible_status', \__('admin.models.visible_status'))
                ->states(\__('admin.switch_visible_statuses'))
                ->default(true)
            ;
        });
        $form->row(function(Form\Row $form) use ($id) {
            $form->width(4);
            /* @var $form Form */
            $form
                ->text('name', \__('admin.models.course.name'))
                ->rules(['string', 'max:255'])
                ->required()
            ;
            $form
                ->text('slug', \__('admin.models.slug'))
                ->creationRules(['nullable', 'string', 'max:255', Rule::unique($this->model->getTable(), 'slug')])
                ->updateRules(['nullable', 'string', 'max:255', Rule::unique($this->model->getTable(), 'slug')->ignore($id)])
            ;
            $form->multipleSelect('leads', \__('admin.models.course.leads'))
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::LEADS->value]))
                ->options(function($_, Form\Field\MultipleSelect $select) {
                    /* @var $this Course */

                    $olds = \old('leads');
                    if (null !== $olds) {
                        $values = Lead::query()->whereKey($olds)->pluck('name', 'id')->toArray();
                    } else {
                        $values = $this->leads->pluck('name', 'id')->toArray();
                    }
                    $select->value(\array_keys($values));

                    return $values;
                })
            ;
        });
        $form->row(function(Form\Row $form) {
            /* @var $form Form */
            $form
                ->ckeditor('description', \__('admin.models.course.description'))
                ->rules(['nullable', 'string', 'max:' . (2**31)])
            ;
        });
        if ($form->isEditing()) {
            $form->row(function(Form\Row $form) {
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
        $model = $this->model::query()->with('leads')->findOrFail($id);
        $show = new Show($model);
        $show->field('id', \__('admin.models.id'));
        $show
            ->field('visible_status', \__('admin.models.visible_status'))
            ->as(fn($status) => $status
                ? \__('admin.switch_visible_statuses.on.text')
                : \__('admin.switch_visible_statuses.off.text')
            )
        ;
        $show->divider();
        $show->field('name', \__('admin.models.course.name'));
        $show->field('slug', \__('admin.models.slug'));
        $show->field('order_column', \__('admin.models.order_column'));
        $show->divider();
        $show->field('description', \__('admin.models.course.description'))->unescape();
        $show
            ->field('leads', \__('admin.models.course.leads'))
            ->as(function(Collection $leads) {
                if (isset($lead) && $leads->isNotEmpty()) {
                    return (new Table([
                        \__('admin.models.id'),
                        \__('admin.models.lead.name'),
                        \__('admin.models.lead.photo'),
                    ], $leads->map(function(Lead $lead) {
                        return [
                            $lead->getKey(),
                            $lead->name,
                            $lead->photo ? \sprintf('<img src="%s" alt="photo">', $lead->photo) : '-',
                        ];
                    })))->render();
                }

                return '-';
            })
            ->unescape()
        ;
        $show->divider();
        $show->field('created_at', \__('admin.created_at'));
        $show->field('updated_at', \__('admin.updated_at'));

        return $show;
    }
}
