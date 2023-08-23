<?php

declare(strict_types=1);

namespace App\Repositories\CompetitionWork;

use App\Contracts\AbstractRepository;
use App\Models\CompetitionWork\WorkAuthor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Connection;

/**
 * Class WorkAuthorRepository
 * @package App\Repositories\CompetitionWork
 *
 * @extends \App\Contracts\AbstractRepository<\App\Models\CompetitionWork\WorkAuthor>
 */
final class WorkAuthorRepository extends AbstractRepository
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection, WorkAuthor::class);
    }

    public function firstOrCreate(User $user, string $name, Carbon $birthDate): WorkAuthor
    {
        $author = $this->createQuery()
            ->where(['user_id' => $user->id, 'birth_date' => $birthDate])
            ->nameLike($name)
            ->first()
        ;

        if (null !== $author) {
            return $author;
        }

        $author = new WorkAuthor();

        $author->name = $name;
        $author->birth_date = $birthDate;

        $author->user()->associate($user);

        $author->save();

        return $author;
    }
}
