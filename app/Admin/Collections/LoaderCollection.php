<?php

declare(strict_types=1);

namespace App\Admin\Collections;

use Illuminate\Http\Resources\Json\ResourceCollection;

final class LoaderCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return $this->collection->map(fn ($collection) => [
            'id' => $collection->id,
            'text' => $collection->text,
        ])->toArray();
    }
}
