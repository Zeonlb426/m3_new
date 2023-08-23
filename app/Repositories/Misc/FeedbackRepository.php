<?php

declare(strict_types=1);

namespace App\Repositories\Misc;

use App\Contracts\AbstractRepository;
use App\Exceptions\FeedbackFrequencyException;
use App\Models\Misc\Feedback;
use App\Models\User;
use Illuminate\Database\Connection;

/**
 * Class FeedbackRepository
 * @package App\Repositories
 *
 * @extends \App\Contracts\AbstractRepository<Feedback>
 */
final class FeedbackRepository extends AbstractRepository
{
    public const FREQUENCY_TIMEOUT = 1 * 60;

    public function __construct(Connection $connection)
    {
        parent::__construct($connection, Feedback::class);
    }

    /**
     * @param string $name
     * @param string $email
     * @param string $content
     * @param \App\Models\User|null $authUser
     * @return bool
     * @throws \App\Exceptions\FeedbackFrequencyException
     * @throws \Throwable
     */
    public function create(string $name, string $email, string $content, ?User $authUser = null): bool
    {
        $lastFeedback = Feedback::whereEmail($email)->latest()->first();

        if (null !== $lastFeedback && $lastFeedback->created_at->gt(\now()->subRealSeconds(self::FREQUENCY_TIMEOUT))) {
            throw (new FeedbackFrequencyException())->setLastCreated($lastFeedback->created_at);
        }

        $feedback = new Feedback();
        $feedback->name = $name;
        $feedback->email = $email;
        $feedback->content = $content;

        if (null !== $authUser) {
            $feedback->user()->associate($authUser);
        }

        $this->save($feedback);

        return true;
    }
}
