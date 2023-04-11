<?php

declare(strict_types=1);

namespace Creatortsv\Scheduler\Provider;

use Creatortsv\Scheduler\Registrar\ScheduleRegistrarInterface;

interface ScheduleProviderInterface
{
    public function boot(ScheduleRegistrarInterface $registrar): void;
}
