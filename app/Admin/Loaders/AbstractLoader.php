<?php

declare(strict_types=1);

namespace App\Admin\Loaders;

use App\Admin\Collections\LoaderCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

abstract class AbstractLoader implements LoaderInterface
{
    abstract protected function getBuilder(): Builder;

    protected function getSearchKey(): string
    {
        return 'title';
    }

    protected function getColumns(): array
    {
        return [
            'id' => 'id',
            'title' => 'text',
        ];
    }

    public function loading(string $q): LoaderCollection
    {
        $builder = $this->getBuilder();
        $columns = [];

        foreach ($this->getColumns() as $key => $value) {
            $columns[] = DB::raw("$key as $value");
        }

        $builder = $builder->select($columns);

        if (false === empty($q)) {
            $builder->where($this->getSearchKey(), 'ilike', "%$q%");
        }

        if (false === empty($exclude = \request('exclude'))) {
            $builder->whereKeyNot(\explode(',', $exclude));
        }

        return new LoaderCollection($builder->paginate());
    }
}
