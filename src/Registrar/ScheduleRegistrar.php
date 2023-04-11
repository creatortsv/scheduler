<?php

declare(strict_types=1);

namespace Creatortsv\Scheduler\Registrar;

use Cron\CronExpression;
use SplObjectStorage;

class ScheduleRegistrar implements ScheduleRegistrarInterface
{
    private SplObjectStorage $storage;

    public function __construct(private readonly string $defaultExpr = '* * * * *')
    {
        $this->storage = new SplObjectStorage();
    }

    public function register(callable $callable): CronExpression
    {
        $this->storage[$expr = new CronExpression($this->defaultExpr)] = $callable;

        return $expr;
    }

    public function current(): callable
    {
        return $this->storage->getInfo();
    }

    public function next(): void
    {
        $this->storage->next();
    }

    public function key(): CronExpression
    {
        return $this->storage->current();
    }

    public function valid(): bool
    {
        return $this->storage->valid();
    }

    public function rewind(): void
    {
        $this->storage->rewind();
    }

    public function count(): int
    {
        return $this->storage->count();
    }
}
