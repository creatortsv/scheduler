<?php

declare(strict_types=1);

namespace Creatortsv\Scheduler\Tests\Moderator;

use Creatortsv\Scheduler\Moderator\ScheduleModerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ScheduleModeratorTest extends TestCase
{
    public static function dataProvider(): array
    {
        $one = fn () => true;
        $two = fn () => true;

        return [
            'one moderator' => [$one],
            'two moderator' => [$one, $two],
        ];
    }

    #[DataProvider('dataProvider')]
    public function testCount(callable ...$moderators): void
    {
        $moderator = new ScheduleModerator();

        array_walk($moderators, $moderator->register(...));

        $this->assertSame(count($moderators), $moderator->count());
    }

    public function testCountWithSameCallableTwice(): void
    {
        $moderator = new ScheduleModerator();
        $callables = [$fn = fn () => true, $fn];

        array_walk($callables, $moderator->register(...));

        $this->assertSame(1, $moderator->count());
    }

    public function testRegister(): void
    {
        $moderator = new ScheduleModerator();
        $moderator->register(fn () => true);

        $this->assertCount(1, $moderator);
    }
}
