<?php

namespace App\Admin\Controllers\Misc;

use App\Admin\Controllers\Api\DropDownListLoaderController;
use App\Admin\Exporters\ClearHTMLWithBOMExporter;
use App\Enums\LoaderType;
use App\Enums\MorphMapperTarget;
use App\Enums\User\ActionType;
use App\Models\User;
use App\Models\User\UserActivity;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;

class LikeController extends AdminController
{
    public function __construct(
        private readonly UserActivity $model
    ) { $this->title = \__('admin.titles.likes'); }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        $grid = new Grid($this->model);
        $grid
            ->model()
            ->where('action_type', ActionType::LIKE->value)
            ->orderByDesc('created_at')
            ->with(['user', 'interacted'])
        ;

        $grid->disableBatchActions()->disableActions()->disableCreateButton()->disableColumnSelector();

        $grid->exporter((new ClearHTMLWithBOMExporter($grid))->filename('likes'));
        $grid->filter(function(Grid\Filter $filter) {
            $filter->disableIdFilter();

            $filter
                ->in('user_id', \__('admin.models.user_activity.user'))
                ->multipleSelect(function($selectedValues) {
                    /* @var $this Grid\Filter\In */
                    if (!empty($selectedValues)) {
                        return User::query()->whereKey($selectedValues)->get()->pluck('name', 'id')->toArray();
                    }
                    return [];
                })
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::USERS->value]))
            ;
            $filter
                ->in('interacted_type', \__('admin.models.user_activity.interacted_type'))
                ->multipleSelect(MorphMapperTarget::likesAvailablePairs())
            ;
            $filter->equal('interacted_id', \__('admin.models.user_activity.interacted_id'));
            $filter->between('created_at', \__('admin.created_at'))->datetime();
        });

        $grid
            ->column('user.name', \__('admin.models.user_activity.user'))
            ->display(function() {
                /* @var $this UserActivity */

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
        $grid
            ->column('interacted_type', \__('admin.models.user_activity.interacted_type'))
            ->display(function() {
                /* @var $this UserActivity */

                return $this->interacted->targetLabel();
            })
        ;
        $grid
            ->column('interacted_id', \__('admin.models.user_activity.interacted_id'))
            ->display(function() {
                /* @var $this UserActivity */

                $routePart = match ($this->interacted_type) {
                    MorphMapperTarget::NEWS->value => 'news',
                    MorphMapperTarget::MASTER_CLASS->value => 'master-classes',
                    MorphMapperTarget::SUCCESS_HISTORY->value => 'success-histories',
                    MorphMapperTarget::WORK->value => 'works',
                    default => null
                };

                if (null === $routePart) {
                    return $this->interacted_id;
                }

                return \sprintf(
                    '<a href="%s?id=%s" target="_blank">%s</a>',
                    \route(admin_get_route(\sprintf('%s.index', $routePart))),
                    $this->interacted_id,
                    \sprintf('%s (#%s)', $this->interacted->targetTitle(), $this->interacted_id)
                );
            })
        ;
        $grid->column('created_at', \__('admin.created_at'))->default()->sortable();

        return $grid;
    }
}
