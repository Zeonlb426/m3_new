<?php

declare(strict_types = 1);

namespace App\Exceptions;

use Carbon\Carbon;

final class FeedbackFrequencyException extends \Exception
{
    private ?Carbon $lastCreated = null;

    /**
     * @return \Carbon\Carbon|null
     */
    public function getLastCreated(): ?Carbon
    {
        return $this->lastCreated;
    }

    /**
     * @param \Carbon\Carbon|null $lastCreated
     * @return $this
     */
    public function setLastCreated(?Carbon $lastCreated): self
    {
        $this->lastCreated = $lastCreated;

        return $this;
    }
}
