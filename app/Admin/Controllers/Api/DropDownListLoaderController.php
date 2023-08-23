<?php

declare(strict_types=1);

namespace App\Admin\Controllers\Api;

use App\Admin\Collections\LoaderCollection;
use App\Admin\Loaders\LoaderFactory;
use Encore\Admin\Controllers\AdminController;
use Illuminate\Http\Request;

final class DropDownListLoaderController extends AdminController
{
    public const ROUTE_NAME = 'drop_down_list_loader';

    /**
     * @param \Illuminate\Http\Request $request
     * @param string|null $type
     * @return \App\Admin\Collections\LoaderCollection
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __invoke(Request $request, ?string $type): LoaderCollection
    {
        return LoaderFactory::create($type)->loading((string) $request->get('q', ''));
    }
}
