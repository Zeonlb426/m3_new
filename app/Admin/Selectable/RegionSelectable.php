<?php

declare(strict_types=1);

namespace App\Admin\Selectable;

use App\Models\Location\Region;
use Encore\Admin\Grid\Selectable;

/**
 * Class RegionSelectable
 * @package App\Admin\Selectable
 */
final class RegionSelectable extends Selectable
{
    public $model = Region::class;

    protected $multiple = false;

    /**
     * @return void
     */
    public function make(): void
    {
        $this->column('title', \__('admin.models.region.title'))->sortable();
        $this->column('code', \__('admin.models.region.code'))->sortable();
    }
}
