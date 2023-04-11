<?php

declare(strict_types=1);

namespace Creatortsv\Scheduler\Registrar;

use Countable;
use Cron\CronExpression;
use Iterator;

interface ScheduleRegistrarInterface extends Iterator, Countable
{
    public function register(callable $callable): CronExpression;

    public function current(): callable;

    public function key(): CronExpression;
}
