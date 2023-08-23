<?php

declare(strict_types=1);

namespace App\Components\MediaLibrary;

use Illuminate\Support\Str;

final class UniqidFileNamer
{
    public function name(string $originalFileName): string
    {
        $ext = \pathinfo($originalFileName, \PATHINFO_EXTENSION);

        return \str_replace('.', '', \uniqid('', true))
            . (Str::isNotEmpty($ext) ? ('.' . $ext) : '')
        ;
    }
}
