<?php

declare(strict_types = 1);

namespace App\Admin\Loaders\Pages;

use App\Admin\Collections\LoaderCollection;
use App\Admin\Loaders\AbstractLoader;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class UserLoader
 * @package App\Admin\Loaders\Pages
 */
final class UserLoader extends AbstractLoader
{
    protected function getBuilder(): Builder
    {
        return User::query();
    }

    public function loading(string $q): LoaderCollection
    {
        $builder = $this
            ->getBuilder()
            ->select([ 'id', \DB::raw('concat(first_name, \' \', last_name) as text') ]);

        if (false === empty($q)) {
            /* @var $builder \Illuminate\Database\Eloquent\Builder|User */
            $builder->nameLike($q);
        }

        return new LoaderCollection($builder->paginate());
    }
}
