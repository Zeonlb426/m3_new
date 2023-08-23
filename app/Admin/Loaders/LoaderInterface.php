<?php

declare(strict_types=1);

namespace App\Admin\Loaders;

use App\Admin\Collections\LoaderCollection;

interface LoaderInterface
{
    public function loading(string $q): LoaderCollection;
}
