<?php

declare(strict_types=1);

namespace Creatortsv\Scheduler\Moderator;

use Countable;
use Iterator;

interface ScheduleModeratorInterface extends Iterator, Countable
{
    public function register(callable $moderator): void;
}
