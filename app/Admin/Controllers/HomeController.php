<?php

declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Layout\Content;

/**
 * Class HomeController
 * @package App\Admin\Controllers
 */
final class HomeController extends Controller
{
    /**
     * @param \Encore\Admin\Layout\Content $content
     *
     * @return \Illuminate\Http\RedirectResponse|\Encore\Admin\Layout\Content
     */
    public function index(Content $content): Content|\Illuminate\Http\RedirectResponse
    {
        /** @var \Encore\Admin\Auth\Database\Administrator $administrator */
        $administrator = \auth('admin')->user();

        $menuQuery = Menu::query();
        $menuQuery->select('admin_menu.*');
        $menuQuery->leftJoin('admin_role_menu', 'admin_menu.id', '=', 'admin_role_menu.menu_id');
        $menuQuery->where('admin_menu.uri', '!=', '');
        $menuQuery->where('admin_menu.uri', '!=', '/');
        $menuQuery->whereIn('admin_role_menu.role_id', $administrator->roles()->pluck('id')->toArray());
        $menuQuery->orderByDesc('id');

        /** @var \Encore\Admin\Auth\Database\Menu $menuItem */
        $menuItem = $menuQuery->first();

        if ($menuItem === null) {
            return $content
                ->title('Dashboard')
                ->description('Description...')
            ;
        }

        return \redirect(\admin_url($menuItem->uri));
    }
}
