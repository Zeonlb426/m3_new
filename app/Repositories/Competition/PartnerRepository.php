<?php

declare(strict_types=1);

namespace App\Repositories\Competition;

use App\Contracts\AbstractRepository;
use App\Models\Competition\Partner;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class PartnerRepository
 * @package App\Repositories
 *
 * @extends \App\Contracts\AbstractRepository<Partner>
 */
final class PartnerRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, Partner::class);
    }

    public function createApiQuery(): Builder|Partner
    {
        return parent::createQuery()->scopes(Partner::$scopeVisible);
    }

    public function allPaginated(int $limit)
    {
        return $this->createApiQuery()->offsetPaginate($limit);
    }
}
