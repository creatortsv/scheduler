<?php

namespace Creatortsv\Scheduler\Tests;

use Creatortsv\Scheduler\Iterator\ScheduleIterator;
use Creatortsv\Scheduler\Moderator\ScheduleModeratorInterface;
use Creatortsv\Scheduler\Provider\ScheduleProviderInterface;
use Creatortsv\Scheduler\Registrar\ScheduleRegistrarInterface;
use Creatortsv\Scheduler\Scheduler;
use DateTime;
use Exception as Throwable;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

class SchedulerTest extends TestCase
{
    /**
     * @throws Exception
     * @throws Throwable
     */
    public function testRun(): void
    {
        $scheduler = new Scheduler();

        $executed = new stdClass();
        $executed->count = 0;
        $expected = fn () => $executed->count ++ ;

        $cronExpr = $scheduler->getRegistrar()->register($expected);

        $scheduler->at($cronExpr->getNextRunDate());
        $scheduler->add($provider = $this->createMock(ScheduleProviderInterface::class));

        $provider->expects($this->once())->method('boot')->with($scheduler->getRegistrar());

        $scheduler->run();

        $this->assertSame(1, $executed->count);
    }

    /**
     * @throws Exception
     */
    public function testConstruct(): void
    {
        $scheduler = new Scheduler(
            $registrar = $this->createMock(ScheduleRegistrarInterface::class),
            $moderator = $this->createMock(ScheduleModeratorInterface::class),
        );

        $this->assertSame($registrar, $scheduler->getRegistrar());
        $this->assertSame($moderator, $scheduler->getModerator());
    }

    public function testEmptyConstruct(): void
    {
        $scheduler = new Scheduler();

        $this->assertInstanceOf(ScheduleRegistrarInterface::class, $scheduler->getRegistrar());
        $this->assertInstanceOf(ScheduleModeratorInterface::class, $scheduler->getModerator());
    }

    /**
     * @throws Exception
     */
    public function testAdd(): void
    {
        $scheduler = new Scheduler();
        $scheduler->add($this->createMock(ScheduleProviderInterface::class));

        $this->assertCount(1, $scheduler->getProviders());
    }

    /**
     * @throws Exception
     */
    public function testRemove(): void
    {
        $scheduler = new Scheduler();
        $scheduler->add($provider = $this->createMock(ScheduleProviderInterface::class));

        $this->assertCount(1, $scheduler->getProviders());

        $scheduler->remove($provider);

        $this->assertCount(0, $scheduler->getProviders());
    }

    /**
     * @throws Exception
     */
    public function testAddSameProvider(): void
    {
        $scheduler = new Scheduler();
        $scheduler->add($provider = $this->createMock(ScheduleProviderInterface::class));
        $scheduler->add($provider);

        $this->assertCount(1, $scheduler->getProviders());
    }

    /**
     * @throws Exception
     */
    public function testGetProviders(): void
    {
        $scheduler = new Scheduler();
        $scheduler->add($provider = $this->createMock(ScheduleProviderInterface::class));

        $this->assertCount(1, $scheduler->getProviders());
        $this->assertSame($provider, current($scheduler->getProviders()));
        $this->assertEquals([spl_object_id($provider)], array_keys($scheduler->getProviders()));
    }

    /**
     * @throws Exception
     */
    public function testAt(): void
    {
        $timestamp = $this->createMock(DateTime::class);
        $scheduler = new Scheduler();
        $scheduler->at($timestamp);

        $this->assertSame($timestamp, $scheduler->dateTime);
    }

    public function testBoot(): void
    {
        $scheduler = new Scheduler();

        $this->assertInstanceOf(ScheduleIterator::class, $scheduler->boot());
    }

    /**
     * @throws Exception
     */
    public function testGetRegistrar(): void
    {
        $registrar = $this->createMock(ScheduleRegistrarInterface::class);
        $scheduler = new Scheduler($registrar);

        $this->assertSame($registrar, $scheduler->getRegistrar());
    }

    /**
     * @throws Exception
     */
    public function testGetModerator(): void
    {
        $moderator = $this->createMock(ScheduleModeratorInterface::class);
        $scheduler = new Scheduler(moderator: $moderator);

        $this->assertSame($moderator, $scheduler->getModerator());
    }
}
