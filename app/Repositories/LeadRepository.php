<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\AbstractRepository;
use App\Models\Lead;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class LeadRepository
 * @package App\Repositories
 *
 * @extends \App\Contracts\AbstractRepository<Lead>
 */
final class LeadRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, Lead::class);
    }

    public function createApiQuery(): Builder|Lead
    {
        return parent::createQuery()->scopes([Lead::$scopeVisible, 'ordered']);
    }

    public function allPaginated(int $limit)
    {
        return $this->createApiQuery()->offsetPaginate($limit);
    }
}
