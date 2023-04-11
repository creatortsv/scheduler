<?php

declare(strict_types=1);

namespace Creatortsv\Scheduler\Iterator;

use Countable;
use Creatortsv\Scheduler\Moderator\ScheduleModeratorInterface;
use Creatortsv\Scheduler\Registrar\ScheduleRegistrarInterface;
use DateTimeInterface;
use FilterIterator;
use TypeError;

class ScheduleIterator extends FilterIterator implements Countable
{
    public function __construct(
        private readonly ScheduleRegistrarInterface $registrar,
        private readonly ScheduleModeratorInterface $moderator,
        public readonly DateTimeInterface $dateTime,
    ) {
        parent::__construct($this->registrar);
    }

    /**
     * @noinspection PhpConditionAlreadyCheckedInspection
     */
    public function accept(): bool
    {
        if ($accept = $this->registrar->key()->isDue($this->dateTime)) {
            iterator_apply(
                $this->moderator,
                $this->moderate(...),
                [$this->moderator, &$accept],
            );
        }

        return $accept;
    }

    public function count(): int
    {
        return iterator_count($this);
    }

    private function moderate(ScheduleModeratorInterface $moderator, bool &$accept): bool
    {
        try {
            return $accept = $moderator->current()($this->registrar->current());
        } catch (TypeError) {
            return $accept = true;
        }
    }
}
