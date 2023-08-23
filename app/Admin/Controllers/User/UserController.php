<?php

declare(strict_types=1);

namespace App\Admin\Controllers\User;

use App\Admin\Controllers\Api\DropDownListLoaderController;
use App\Admin\Exporters\ClearHTMLWithBOMExporter;
use App\Enums\LoaderType;
use App\Models\Location\City;
use App\Models\Location\Region;
use App\Models\User;
use App\Rules\User\Email;
use App\Rules\User\Phone;
use App\Services\User\UserService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Validation\Rule;

final class UserController extends AdminController
{
    public function __construct(
        private readonly User $model
    ) {
        $this->title = \__('admin.titles.users');
    }

    public function grid(): Grid
    {
        $grid = new Grid($this->model);
        $grid->model()->with(['region', 'city'])->orderByDesc('created_at');
        $defaultAvatar = \config('app.url') . '/vendor/images/default_avatar.jpg';

        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->hideColumns(['id', 'region.title', 'city.title', 'updated_at']);

        $grid->exporter(new ClearHTMLWithBOMExporter($grid));
        $grid->filter(function (Grid\Filter $filter) {
            $filter->where(function ($qb) {
                /* @var $this Grid\Filter\Where */
                /* @var $qb \Illuminate\Database\Eloquent\Builder|User */
                $value = (string)$this->input;

                if (false === empty($value)) {
                    $qb->nameLike($value);
                }
            }, \__('admin.models.user.full_name'), 'full_name');
            $filter->ilike('email', \__('admin.models.user.email'));
            $filter->ilike('phone', \__('admin.models.user.phone'));
            $filter->between('birth_date', \__('admin.models.user.birth_date'))->date();
            $filter
                ->in('region_id', \__('admin.models.user.region'))
                ->multipleSelect(function ($selectedValues) {
                    /* @var $this Grid\Filter\In */
                    if (!empty($selectedValues)) {
                        return Region::query()->whereKey($selectedValues)->pluck('title', 'id')->toArray();
                    }
                    return [];
                })
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::REGION->value]))
            ;
            $filter
                ->in('city_id', \__('admin.models.user.city'))
                ->multipleSelect(function ($selectedValues) {
                    /* @var $this Grid\Filter\In */
                    if (!empty($selectedValues)) {
                        return City::query()->whereKey($selectedValues)->pluck('title', 'id')->toArray();
                    }
                    return [];
                })
                ->config('minimumInputLength', 0)
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::CITY->value]))
            ;
            $filter->between('created_at', \__('admin.created_at'))->datetime();
        });

        $grid->column('id', \__('admin.models.id'))->sortable();
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $grid->column('avatar', \__('admin.models.user.avatar'))->image(null, 75, 75, $defaultAvatar);

        $grid->column('first_name', \__('admin.models.user.first_name'))->editable()->default(\__('admin.messages.empty_value'))->sortable();
        $grid->column('last_name', \__('admin.models.user.last_name'))->editable()->default(\__('admin.messages.empty_value'))->sortable();
        $grid->column('birth_date', \__('admin.models.user.birth_date'))->default()->sortable()->date();
        $grid->column('email', \__('admin.models.user.email'))->default(\__('admin.messages.empty_value'))->sortable();
        $grid->column('phone', \__('admin.models.user.phone'))->default(\__('admin.messages.empty_value'))->sortable();
        $grid->column('region.title', \__('admin.models.user.region'))->default(\__('admin.messages.empty_value'))->sortable();
        $grid->column('city.title', \__('admin.models.user.city'))->default(\__('admin.messages.empty_value'))->sortable();
        $grid->column('created_at', \__('admin.created_at'))->default()->sortable();
        $grid->column('updated_at', \__('admin.updated_at'))->default()->sortable();

        return $grid;
    }

    public function form(): Form
    {
        $form = new Form($this->model);

        $form->disableCreatingCheck()->disableEditingCheck()->disableViewCheck();
        $form->builder()->getFooter()->disableReset();

        $id = $form->isEditing()
            ? (int)\Route::current()->parameter('user')
            : null;

        $form->row(function (Form\Row $form) use ($id) {
            $form->width(6);
            /* @var $form Form */

            $form
                ->text('first_name', \__('admin.models.user.first_name'))
                ->rules(['string', 'max:64'])
            ;
            $form
                ->email('email', \__('admin.models.user.email'))
                ->required()
                ->rules(['required', 'max:64', new Email()])
                ->creationRules([
                    Rule::unique($this->model->getTable(), 'email'),
                ])
                ->updateRules([
                    Rule::unique($this->model->getTable(), 'email')->ignore($id),
                ])
            ;

            $form
                ->text('last_name', \__('admin.models.user.last_name'))
                ->rules(['string', 'max:64'])
            ;
            $form
                ->text('phone', \__('admin.models.user.phone'))
                ->inputmask([
                    'placeholder' => '+7 (___) ___-____',
                    'mask' => '\+\7 \(999\) 999-9999',
                    'autoUnmask' => true,
                    'clearMaskOnLostFocus' => false,
                    'clearIncomplete' => false,
                    'positionCaretOnClick' => 'radix',
                ])
                ->rules(['string', 'max:32', new Phone()])
            ;
        });
        $form->row(function (Form\Row $form) {
            $form->width(4);
            /* @var $form Form */

            $form
                ->date('birth_date', \__('admin.models.user.birth_date'))
                ->options([
                    'format' => 'Y-m-d',
                    'minDate' => \now()->subYears(150)->format('Y-m-d'),
                    'maxDate' => \now()->format('Y-m-d'),
                ])
                ->rules([
                    \sprintf('after:%s', \now()->subYears(150)->format('Y-m-d')),
                    \sprintf('before:%s', \now()->format('Y-m-d')),
                ])
            ;
            $form
                ->select('region_id', \__('admin.models.user.region'))
                ->config('minimumInputLength', 0)
                ->options(function () {
                    /* @var $this User */
                    return $this->region_id ? [$this->region_id => $this->region->title] : [];
                })
                ->ajax(\route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::REGION->value]))
            ;
            $form
                ->select('city_id', \__('admin.models.user.city'))
                ->config('minimumInputLength', 0)
                ->options(function () {
                    /* @var $this User */
                    return $this->city_id ? [$this->city_id => $this->city->title] : [];
                })
                ->ajax(
                    \route(\admin_get_route(DropDownListLoaderController::ROUTE_NAME), ['type' => LoaderType::CITY->value])
                    . '?region_id=" + $(\'select[name="region_id"]\').val() + "'
                )
            ;
        });
        $form->row(function (Form\Row $form) use ($id) {
            /* @var $form Form */

            $form
                ->image('avatar', \__('admin.models.user.avatar'))
                ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
                ->value(User::findOrFail($id)->avatar)
            ;
        });
        $form->row(function (Form\Row $form) {
            $form->width(6);
            /* @var $form Form */

            $form->password('password', \__('admin.password'))->rules(['nullable', 'min:6', 'max:255', 'confirmed']);
            $form->password('password_confirmation', \__('admin.password_confirmation'))->rules(['nullable', 'min:6', 'max:255']);
        });

        if ($form->isEditing()) {
            $form->row(function (Form\Row $form) {
                $form->width(6);
                /* @var $form Form */

                $form->text('created_at', \__('admin.created_at'))->disable();
                $form->text('updated_at', \__('admin.updated_at'))->disable();
            });
        }

        $form->ignore(['password_confirmation', 'avatar']);
        $form->editing(function (Form $form) {
            /* @var $model User */
            $model = $form->model();
            $val = \old('phone', $model->phone);
            $val && $model->phone = preg_replace('/^\+7/', '', $val);
        });

        $form->saving(function (Form $form) {
            $form->input('email', Email::clean(\request('email')));

            $phone = \request('phone');

            if (empty($phone)) {
                return true;
            }

            /* @var $model User */
            $model = $form->model();

            /* @var $field \Encore\Admin\Form\Field */
            $field = $form->fields()->keyBy(fn(Form\Field $field) => $field->variables()['id'])->get('phone');

            $form->isCreating()
                ? $field->rules([Rule::unique($this->model->getTable(), 'phone')])
                : $field->rules([Rule::unique($this->model->getTable(), 'phone')->ignore($model->getKey())]);
            try {
                $cleanPhone = Phone::clean($phone);
                $validator = $field->getValidator([
                    'phone' => $cleanPhone,
                ]);
                $validator->validate();
                $form->input('phone', $cleanPhone);

            } catch (\Illuminate\Validation\ValidationException $e) {
                return \back()->withErrors($e->errors());
            }
            return true;
        });

        $form->saved(function (Form $form): bool {
            if (1 === \count(\Arr::except(\request()->all(), ['_token', '_method']))) {
                return true;
            }

            $request = \request();
            /* @var $model User */
            $model = $form->model();

            if ($request->hasFile('avatar')) {
                \app(UserService::class)->updateAvatar($model, $request->file('avatar'));
            }

            return true;
        });

        return $form;
    }

    public function detail($id): Show
    {
        $show = new Show($this->model::query()->with(['region', 'city'])->findOrFail($id));

        $show->field('id', \__('admin.models.id'));
        $show->field('first_name', \__('admin.models.user.first_name'));
        $show->field('last_name', \__('admin.models.user.last_name'));
        $show->divider();
        $show->field('email', \__('admin.models.user.email'));
        $show->field('phone', \__('admin.models.user.phone'));
        $show->field('avatar', \__('admin.models.user.avatar'))->image();
        $show->divider();
        $show->field('birth_date', \__('admin.models.user.birth_date'));
        $show->field('region.title', \__('admin.models.user.region'));
        $show->field('city.title', \__('admin.models.user.city'));
        $show->divider();
        $show->field('created_at', \__('admin.created_at'));
        $show->field('updated_at', \__('admin.updated_at'));

        return $show;
    }
}
