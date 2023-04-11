<?php

declare(strict_types=1);

namespace Creatortsv\Scheduler\Moderator;

use ArrayIterator;
use IteratorIterator;

class ScheduleModerator extends IteratorIterator implements ScheduleModeratorInterface
{
    private readonly ArrayIterator $storage;

    public function __construct()
    {
        parent::__construct($this->storage = new ArrayIterator());
    }

    /**
     * @param callable(callable $callable): bool $moderator
     */
    public function register(callable $moderator): void
    {
        $this->getInnerIterator()[spl_object_id($moderator(...))] ??= $moderator;
    }

    public function count(): int
    {
        return $this->storage->count();
    }
}
