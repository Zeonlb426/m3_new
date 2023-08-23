<?php

declare(strict_types=1);

namespace App\Jobs\Traits;

use App\Enums\User\ActionType;

trait HasUniqueLike
{
    public function uniqueId(): string
    {
        return \sprintf(
            '%s:%s.%s-%s',
            $this->user->getKey(),
            $this->model->getMorphClass(),
            $this->model->getKey(),
            ActionType::LIKE->name
        );
    }
}
