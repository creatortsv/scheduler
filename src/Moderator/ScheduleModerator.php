<?php

declare(strict_types=1);

namespace Creatortsv\Scheduler\Moderator;

use ArrayIterator;
use IteratorIterator;

/**
 * @template-implements ScheduleModeratorInterface<callable(callable $callable): bool>
 */
class ScheduleModerator extends IteratorIterator implements ScheduleModeratorInterface
{
    public function __construct()
    {
        parent::__construct(new ArrayIterator());
    }

    public function register(callable $moderator): void
    {
        $this->getInnerIterator()[spl_object_id($moderator(...))] ??= $moderator;
    }

    public function count(): int
    {
        return iterator_count($this->getInnerIterator());
    }
}
