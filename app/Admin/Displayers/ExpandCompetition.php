<?php

declare(strict_types = 1);

namespace App\Admin\Displayers;

use App\Models\Competition\Competition;
use Encore\Admin\Show;
use Illuminate\Contracts\Support\Renderable;

final class ExpandCompetition implements Renderable
{
    protected string|Competition $model = Competition::class;

    public function render($id = null): string
    {
        $model = $this->model::query()->with([
            'ageGroupsAll',
            'themes',
            'leadsAll',
            'masterClassesAll',
            'partnersAll',
            'workTypesAll',
            'sharing'
        ])->findOrFail($id);

        $show = new Show($model);
        $show->panel()->tools(function(Show\Tools $tools): void {
            $tools
                ->disableDelete()
                ->disableEdit()
                ->disableList()
            ;
        });

        $show->field('period', \__('admin.models.competition.period'));
        $show->field('short_content', \__('admin.models.competition.content'))->unescape();
        $show->field('content', \__('admin.models.competition.content'))->unescape();
        $show->field('cover', \__('admin.models.competition.cover'))->image();
        $show->field('sharing.title', \__('admin.models.sharing.title'));
        $show->field('sharing.description', \__('admin.models.sharing.description'))->unescape();
        $show->field('sharing.image', \__('admin.models.sharing.image'))->image();

        $show
            ->field('workTypesAll', \__('admin.models.competition.work_types.title'))
            ->as(function() {
                /* @var $this Competition */
                return $this->workTypesAll->pluck('title')->implode('; ');
            })
            ->unescape()
        ;
        $show
            ->field('ageGroupsAll', \__('admin.models.competition.age_groups.title'))
            ->as(function() {
                /* @var $this Competition */
                return $this->ageGroupsAll->pluck('title')->implode('; ');
            })
            ->unescape()
        ;
        $show
            ->field('themes', \__('admin.models.competition.themes.title'))
            ->as(function() {
                /* @var $this Competition */
                return $this->themes->pluck('title')->implode('; ');
            })
            ->unescape()
        ;
        $show
            ->field('leadsAll', \__('admin.models.competition.leads.title'))
            ->as(function() {
                /* @var $this Competition */
                return $this->leadsAll->pluck('name')->implode('; ');
            })
            ->unescape()
        ;
        $show
            ->field('masterClassesAll', \__('admin.models.competition.master_classes.title'))
            ->as(function() {
                /* @var $this Competition */
                return $this->masterClassesAll->pluck('title')->implode('; ');
            })
            ->unescape()
        ;
        $show
            ->field('partnersAll', \__('admin.models.competition.partners.title'))
            ->as(function() {
                /* @var $this Competition */
                return $this->partnersAll->pluck('title')->implode('; ');
            })
            ->unescape()
        ;

        return $show->render();
    }
}
