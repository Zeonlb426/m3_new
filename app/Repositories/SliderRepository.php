<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\AbstractRepository;
use App\Models\Slider;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class SliderRepository
 * @package App\Repositories
 *
 * @extends \App\Contracts\AbstractRepository<Slider>
 */
final class SliderRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, Slider::class);
    }

    public function createApiQuery(): Builder|Slider
    {
        return parent::createQuery()->scopes([Slider::$scopeVisible, 'ordered']);
    }

    public function all(): Collection
    {
        return $this->createApiQuery()->get();
    }
}
