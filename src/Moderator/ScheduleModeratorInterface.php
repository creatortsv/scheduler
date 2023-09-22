<?php

declare(strict_types=1);

namespace Creatortsv\Scheduler\Moderator;

use Countable;
use Iterator;

/**
 * @template TModerator of callable(callable $callable): bool
 * @template-implements Iterator<array-key, TModerator>
 */
interface ScheduleModeratorInterface extends Iterator, Countable
{
    public function register(callable $moderator): void;
}
