<?php /** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

namespace App\Admin\Controllers\Competition;

use App\Admin\Controllers\Api\DropDownListLoaderController;
use App\Enums\Competition\TileSize;
use App\Enums\LoaderType;
use App\Models\Competition\Theme;
use App\Models\Lead;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ThemeController extends AdminController
{
    public function __construct(
        private readonly Theme $model
    ) { $this->title = \__('admin.titles.themes'); }

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
            $filter->ilike('title', \__('admin.models.theme.title'));
            $filter->ilike('description', \__('admin.models.theme.description'));
        });

        $grid->column('title', \__('admin.models.theme.title'))->editable();
        $grid
            ->column('description', \__('admin.models.theme.description'))
            ->display(fn($data) => $data
                ? \Str::limit(\strip_tags($data), 250)
                : \__('admin.messages.empty_value')
            )
        ;
        $grid->column('cover', \__('admin.models.theme.cover'))->image();

        return $grid;
    }

    protected function form(): Form
    {
        $form = new Form($this->model);

        $form->disableCreatingCheck()->disableEditingCheck()->disableViewCheck();
        $form->builder()->getFooter()->disableReset();

        $form->row(function(Form\Row $form) {
            /* @var $form Form */

            $form
                ->text('title', \__('admin.models.theme.title'))
                ->rules(['required', 'string', 'max:255'])
                ->required()
            ;

            $form->multipleSelect('leads', \__('admin.models.theme.leads'))
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::LEADS->value]))
                ->options(function($_, Form\Field\MultipleSelect $select) {
                    /* @var $this Theme */

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
        $form->row(
            fn(Form\Row|Form $form)
            => $form->divider(\__('admin.messages.tile_images'))
        );
        $form->row(function(Form\Row $form) {
            $form->width(6);
            /* @var $form Form */

            $form
                ->select('tile_size', \__('admin.models.theme.tile_size'))
                ->options(TileSize::labels())
                ->default(TileSize::SMALL->value)
                ->required()
            ;
            $form
                ->mediaLibrary('tile', \__('admin.models.theme.tile'))
                ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
                ->required()
            ;
        });
        $form->row(
            fn(Form\Row|Form $form)
            => $form->divider(\__('admin.messages.show_images'))
        );
        $form->row(function(Form\Row $form) {
            /* @var $form Form */

            $form
                ->mediaLibrary('cover', \__('admin.models.theme.cover'))
                ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
                ->required()
            ;

            $form
                ->ckeditor('description', \__('admin.models.theme.description'))
                ->rules(['required', 'string', 'max:' . (2**31)])
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
    protected function detail(string|int $id): Show
    {
        $model = $this->model::query()->findOrFail($id);
        $show = new Show($model);

        $show->field('id', \__('admin.models.id'));
        $show->field('title', \__('admin.models.theme.title'));
        $show
            ->field('leads', \__('admin.models.theme.leads'))
            ->as(function() {
                /* @var $this Theme */
                return $this->leads->pluck('name')->implode('<br>');
            })
            ->unescape()
        ;
        $show->field('description', \__('admin.models.theme.description'))->unescape();
        $show->field('cover', \__('admin.models.theme.cover'))->image();
        $show->field('tile', \__('admin.models.theme.tile'))->image();

        return $show;
    }
}
