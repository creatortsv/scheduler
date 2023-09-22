<?php

declare(strict_types=1);

namespace Creatortsv\Scheduler;

use Creatortsv\Scheduler\Iterator\ScheduleIterator;
use Creatortsv\Scheduler\Moderator\ScheduleModerator;
use Creatortsv\Scheduler\Moderator\ScheduleModeratorInterface;
use Creatortsv\Scheduler\Provider\ScheduleProviderInterface;
use Creatortsv\Scheduler\Registrar\ScheduleRegistrar;
use Creatortsv\Scheduler\Registrar\ScheduleRegistrarInterface;
use DateTime;
use DateTimeInterface;

class Scheduler
{
    public DateTimeInterface $dateTime;

    /**
     * @var array<int, ScheduleProviderInterface>
     */
    private array $providers = [];

    public function __construct(
        private readonly ScheduleRegistrarInterface $registrar = new ScheduleRegistrar(),
        private readonly ScheduleModeratorInterface $moderator = new ScheduleModerator(),
    ) {
    }

    /**
     * @return array<int, ScheduleProviderInterface>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    final public function add(ScheduleProviderInterface $provider): self
    {
        $this->providers[spl_object_id($provider)] ??= $provider;

        return $this;
    }

    final public function remove(ScheduleProviderInterface $provider): self
    {
        if (isset($this->providers[$id = spl_object_id($provider)])) {
            unset($this->providers[$id]);
        }

        return $this;
    }

    final public function boot(): ScheduleIterator
    {
        $registrar = clone $this->registrar;

        array_walk($this->providers, static fn (ScheduleProviderInterface $p) => $p->boot($registrar));

        $registrar->rewind();

        return new ScheduleIterator(
            $registrar,
            $this->moderator,
            $this->dateTime ??= new DateTime(),
        );
    }

    public function at(DateTimeInterface $dateTime): self
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    public function getRegistrar(): ScheduleRegistrarInterface
    {
        return $this->registrar;
    }

    public function getModerator(): ScheduleModeratorInterface
    {
        return $this->moderator;
    }

    public function run(): void
    {
        iterator_apply($iterator = $this->boot(), $this->exec(...), [$iterator]);
    }

    private function exec(ScheduleIterator $iterator): bool
    {
        $iterator->current()();

        return true;
    }
}
