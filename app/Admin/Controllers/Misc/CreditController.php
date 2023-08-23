<?php

namespace App\Admin\Controllers\Misc;

use App\Admin\Controllers\Api\DropDownListLoaderController;
use App\Admin\Exporters\ClearHTMLWithBOMExporter;
use App\Enums\LoaderType;
use App\Enums\User\ActionType;
use App\Models\User;
use App\Models\User\UserTotalCredit;
use App\Repositories\User\UserActivityRepository;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;

class CreditController extends AdminController
{
    public function __construct(
        private readonly UserTotalCredit $model
    ) { $this->title = \__('admin.titles.credits'); }

    protected function grid(): Grid
    {
        $grid = new Grid($this->model);
        $grid
            ->model()
            ->with('user')
            ->orderBy('user_id')
            ->select('user_total_credits.*')
            ->selectSub(
                UserActivityRepository::getActivitiesCountQuery()->whereRaw('ua.user_id = user_total_credits.user_id'),
                'likes_count'
            )
            ->selectSub(
                UserActivityRepository::getActivitiesCountQuery(ActionType::ADD_WORK)->whereRaw('ua.user_id = user_total_credits.user_id'),
                'works_count'
            )
        ;

        $grid->disableBatchActions()->disableActions()->disableCreateButton()->disableColumnSelector();

        $grid->exporter((new ClearHTMLWithBOMExporter($grid))->filename('credits'));
        $grid->filter(function(Grid\Filter $filter) {
            $filter->disableIdFilter();

            $filter
                ->in('user_id', \__('admin.models.user_total_credit.user'))
                ->multipleSelect(function ($selectedValues) {
                    /* @var $this Grid\Filter\In */
                    if (!empty($selectedValues)) {
                        return User::query()->whereKey($selectedValues)->get()->pluck('name', 'id')->toArray();
                    }
                    return [];
                })
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::USERS->value]))
            ;
            $filter->gt('count_register', \__('admin.models.user_total_credit.count_register'))->integer();
            $filter->gt('count_likes', \__('admin.models.user_total_credit.count_likes'))->integer();
            $filter->gt('count_works', \__('admin.models.user_total_credit.count_works'))->integer();
            $filter->gt('count_total', \__('admin.models.user_total_credit.count_total'))->integer();
        });

        $grid
            ->column('user.name', \__('admin.models.user_total_credit.user'))
            ->display(function() {
                /* @var $this UserTotalCredit */

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
        $grid->column('count_register', \__('admin.models.user_total_credit.count_register'))->sortable();
        $grid->column('likes_count', \__('admin.models.user_total_credit.likes_count'))->sortable();
        $grid->column('count_likes', \__('admin.models.user_total_credit.count_likes'))->sortable();
        $grid->column('works_count', \__('admin.models.user_total_credit.works_count'))->sortable();
        $grid->column('count_works', \__('admin.models.user_total_credit.count_works'))->sortable();
        $grid->column('count_total', \__('admin.models.user_total_credit.count_total'))->sortable();

        return $grid;
    }
}
