<?php

namespace App\Admin\Controllers;

use App\Settings\CountersSettings;
use App\Settings\MainTextsSettings;
use App\Settings\PointsExchangeSettings;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\Settings;

class SettingsController extends AdminController
{
    private string $requestGroupParam = 'group';
    private string $group;
    private array $availableSettings;

    public function __construct()
    {
        $this->availableSettings = [
            MainTextsSettings::class,
            PointsExchangeSettings::class,
            CountersSettings::class,
        ];
        $this->group = $this->currentGroup();

        $this->title = \__(\sprintf('admin.titles.settings.%s', $this->group));
    }

    /**
     * @param \Encore\Admin\Layout\Content $content
     * @return \Encore\Admin\Layout\Content
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function index(Content $content): Content
    {
        $form = new Form(new SettingsProperty());
        $form->disableCreatingCheck()->disableEditingCheck()->disableViewCheck();
        $form->builder()->getFooter()->disableReset();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete()->disableView()->disableList();
        });
        $form->setAction(\route(\admin_get_route('settings.update'), ['setting' => $this->group]));

        return $content
            ->title($this->title())
            ->description(trans('admin.edit'))
            ->row(function(Row $row) {
                $row->column(
                    12,
                    \sprintf(
                        <<<HTML
                            <div class="nav-tabs-custom" style="margin-bottom: 0!important;">
                                <ul class="nav nav-tabs">
                                  %s
                                </ul>
                            </div>
                        HTML,
                        \implode(
                            \array_map(
                                fn($s) => /* @var $s Settings */
                                \sprintf(
                                    <<<HTML
                                        <li class="%s"><a href="%s">%s</a></li>
                                    HTML,
                                    $s::group() === $this->group ? 'active' : '',
                                    $this->currentUrl($s::group()),
                                    \__(\sprintf('admin.titles.settings.%s', $s::group()))
                                ),
                                $this->availableSettings
                            )
                        )
                    )
                );
            })
            ->row(
                match($this->group) {
                    CountersSettings::group() => $this->formCounters($form),
                    PointsExchangeSettings::group() => $this->formPoint($form),
                    default => $this->formTexts($form)
                }
            );
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function update($id): \Illuminate\Http\RedirectResponse
    {
        $instance = null;
        foreach ($this->availableSettings as $setting) {
            /* @var $setting string|Settings */
            if ($setting::group() === $id) {
                $instance = \app()->make($setting)->refresh();
                break;
            }
        }

        if (null === $instance) {
            \admin_toastr('Неподдерживаемый тип настроек', 'danger');
            return \redirect(\route(\admin_get_route('settings.index')));
        }

        $instance->lock();
        $data = \request()->all();
        foreach ($data as $name => $value) {
            if (\property_exists($instance, $name)) {
                $instance->{$name} = $value;
            }
        }
        $instance->save();
        $instance->unlock();

        \admin_toastr(\__('admin.save_succeeded'));
        return \redirect($this->currentUrl($id));
    }

    /**
     * @param \Encore\Admin\Form $form
     * @return \Encore\Admin\Form
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function formCounters(Form $form): Form
    {
        $data = \app()->make(CountersSettings::class)->refresh();

        $form
            ->number('fake_credits', \__(\sprintf('admin.models.settings.%s.fake_credits', $this->group)))
            ->value($data->fake_credits)
        ;
        $form
            ->number('fake_likes', \__(\sprintf('admin.models.settings.%s.fake_likes', $this->group)))
            ->value($data->fake_likes)
        ;

        return $form;
    }

    /**
     * @param \Encore\Admin\Form $form
     * @return \Encore\Admin\Form
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function formTexts(Form $form): Form
    {
        $data = \app()->make(MainTextsSettings::class)->refresh();

        $form
            ->text('meta_title', \__(\sprintf('admin.models.settings.%s.meta_title', $this->group)))
            ->value($data->meta_title)
        ;
        $form
            ->text('meta_description', \__(\sprintf('admin.models.settings.%s.meta_description', $this->group)))
            ->value($data->meta_description)
        ;
        $form
            ->text('meta_keywords', \__(\sprintf('admin.models.settings.%s.meta_keywords', $this->group)))
            ->value($data->meta_keywords)
        ;
        $form
            ->text('sharing_title', \__(\sprintf('admin.models.settings.%s.sharing_title', $this->group)))
            ->value($data->sharing_title)
        ;
        $form
            ->text('sharing_description', \__(\sprintf('admin.models.settings.%s.sharing_description', $this->group)))
            ->value($data->sharing_description)
        ;
        $form
            ->image('sharing_image', \__(\sprintf('admin.models.settings.%s.sharing_image', $this->group)))
            ->rules(['nullable', 'image', 'max:' . (5 * 1024)])
            ->value($data->sharing_image)
        ;

        return $form;
    }

    /**
     * @param \Encore\Admin\Form $form
     * @return \Encore\Admin\Form
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function formPoint(Form $form): Form
    {
        $data = \app()->make(PointsExchangeSettings::class)->refresh();

        $form
            ->number('exchange_rate', \__(\sprintf('admin.models.settings.%s.exchange_rate', $this->group)))
            ->value($data->exchange_rate)
        ;
        $form
            ->number('points_registration', \__(\sprintf('admin.models.settings.%s.points_registration', $this->group)))
            ->value($data->points_registration)
        ;
        $form
            ->number('points_like', \__(\sprintf('admin.models.settings.%s.points_like', $this->group)))
            ->value($data->points_like)
        ;
        $form
            ->number('points_work_add', \__(\sprintf('admin.models.settings.%s.points_work_add', $this->group)))
            ->value($data->points_work_add)
        ;

        return $form;
    }

    private function currentGroup(): string
    {
        $group = \request($this->requestGroupParam);

        if (\in_array($group, \array_map(fn($s) => /* @var $s Settings */ $s::group(), $this->availableSettings))) {
            return $group;
        }

        return MainTextsSettings::group();
    }

    private function currentUrl(string $group): string
    {
        return \sprintf(
            '%s?%s=%s',
            \route(\admin_get_route('settings.index')),
            $this->requestGroupParam,
            $group
        );
    }
}
