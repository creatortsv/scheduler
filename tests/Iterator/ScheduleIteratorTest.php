<?php

declare(strict_types=1);

namespace Creatortsv\Scheduler\Tests\Iterator;

use Creatortsv\Scheduler\Iterator\ScheduleIterator;
use Creatortsv\Scheduler\Moderator\ScheduleModeratorInterface;
use Creatortsv\Scheduler\Registrar\ScheduleRegistrarInterface;
use Cron\CronExpression;
use DateTime;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TypeError;

class ScheduleIteratorTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testConstruct(): void
    {
        [, $registrar, $moderator, $timestamp] = $this->getMocks();

        $iterator = new ScheduleIterator($registrar, $moderator, $timestamp);

        $this->assertSame($timestamp, $iterator->dateTime);
    }

    /**
     * @throws Exception
     */
    public function testCount(): void
    {
        [, $registrar, $moderator, $timestamp] = $this->getMocks();

        $iterator = new ScheduleIterator($registrar, $moderator, $timestamp);

        $this->assertCount(0, $iterator);
    }

    /**
     * @throws Exception
     */
    public function testDoesNotAcceptByCron(): void
    {
        [$cronExpr, $registrar, $moderator, $timestamp] = $this->getMocks();

        $iterator = new ScheduleIterator($registrar, $moderator, $timestamp);
        $cronExpr->expects($this->once())->method('isDue')->with($timestamp)->willReturn(false);
        $registrar->expects($this->once())->method('key')->willReturn($cronExpr);

        $this->assertFalse($iterator->accept());
    }

    /**
     * @throws Exception
     */
    public function testAcceptByCron(): void
    {
        [$cronExpr, $registrar, $moderator, $timestamp] = $this->getMocks();

        $iterator = new ScheduleIterator($registrar, $moderator, $timestamp);
        $cronExpr->expects($this->once())->method('isDue')->with($timestamp)->willReturn(true);
        $registrar->expects($this->once())->method('key')->willReturn($cronExpr);

        $this->assertTrue($iterator->accept());
    }

    /**
     * @throws Exception
     */
    public function testAcceptByCronAndModerator(): void
    {
        [$cronExpr, $registrar, $moderator, $timestamp] = $this->getMocks();

        $iterator = new ScheduleIterator($registrar, $moderator, $timestamp);
        $cronExpr->expects($this->once())->method('isDue')->with($timestamp)->willReturn(true);
        $registrar->expects($this->once())->method('key')->willReturn($cronExpr);
        $registrar->expects($this->once())->method('current')->willReturn(fn (): bool => true);
        $moderator->expects($this->exactly(2))->method('valid')->willReturn(true, false);
        $moderator->expects($this->exactly(1))->method('next');
        $moderator->expects($this->exactly(1))->method('current')->willReturn(fn (): bool => true);

        $this->assertTrue($iterator->accept());
    }

    /**
     * @throws Exception
     */
    public function testAcceptByCronAndIncompatibleModerator(): void
    {
        [$cronExpr, $registrar, $moderator, $timestamp] = $this->getMocks();

        $iterator = new ScheduleIterator($registrar, $moderator, $timestamp);
        $cronExpr->expects($this->once())->method('isDue')->with($timestamp)->willReturn(true);
        $registrar->expects($this->once())->method('key')->willReturn($cronExpr);
        $registrar->expects($this->once())->method('current')->willReturn(fn (): bool => true);
        $moderator->expects($this->exactly(2))->method('valid')->willReturn(true, false);
        $moderator->expects($this->exactly(1))->method('next');
        $moderator->expects($this->exactly(1))->method('current')->willReturn(
            fn () => throw new TypeError(),
        );

        $this->assertTrue($iterator->accept());
    }

    /**
     * @throws Exception
     */
    public function testAcceptByCronAndMultipleModerator(): void
    {
        [$cronExpr, $registrar, $moderator, $timestamp] = $this->getMocks();

        $iterator = new ScheduleIterator($registrar, $moderator, $timestamp);
        $cronExpr->expects($this->once())->method('isDue')->with($timestamp)->willReturn(true);
        $registrar->expects($this->once())->method('key')->willReturn($cronExpr);
        $registrar->expects($this->exactly(2))->method('current')->willReturn(fn (): bool => true);
        $moderator->expects($this->exactly(3))->method('valid')->willReturn(true, true, false);
        $moderator->expects($this->exactly(2))->method('next');
        $moderator->expects($this->exactly(2))->method('current')->willReturn(fn (): bool => true);

        $this->assertTrue($iterator->accept());
    }

    /**
     * @throws Exception
     */
    public function testAcceptByCronButFailFirstOfModerators(): void
    {
        [$cronExpr, $registrar, $moderator, $timestamp] = $this->getMocks();

        $iterator = new ScheduleIterator($registrar, $moderator, $timestamp);
        $cronExpr->expects($this->once())->method('isDue')->with($timestamp)->willReturn(true);
        $registrar->expects($this->once())->method('key')->willReturn($cronExpr);
        $registrar->expects($this->exactly(1))->method('current')->willReturn(fn (): bool => true);
        $moderator->expects($this->exactly(1))->method('valid')->willReturn(true);
        $moderator->expects($this->exactly(1))->method('current')->willReturn(
            fn (): bool => false,
        );

        $this->assertFalse($iterator->accept());
    }

    /**
     * @throws Exception
     */
    public function testAcceptByCronButFailLastOfModerators(): void
    {
        [$cronExpr, $registrar, $moderator, $timestamp] = $this->getMocks();

        $iterator = new ScheduleIterator($registrar, $moderator, $timestamp);
        $cronExpr->expects($this->once())->method('isDue')->with($timestamp)->willReturn(true);
        $registrar->expects($this->once())->method('key')->willReturn($cronExpr);
        $registrar->expects($this->exactly(2))->method('current')->willReturn(fn (): bool => true);
        $moderator->expects($this->exactly(2))->method('valid')->willReturn(true, true, false);
        $moderator->expects($this->exactly(1))->method('next');
        $moderator->expects($this->exactly(2))->method('current')->willReturn(
            fn (): bool => true,
            fn (): bool => false,
        );

        $this->assertFalse($iterator->accept());
    }

    /**
     * @return array{
     *     CronExpression|MockObject,
     *     ScheduleRegistrarInterface|MockObject,
     *     ScheduleModeratorInterface|MockObject,
     *     DateTime|MockObject,
     * }
     * @throws Exception
     */
    private function getMocks(): array
    {
        return [
            $this->createMock(CronExpression::class),
            $this->createMock(ScheduleRegistrarInterface::class),
            $this->createMock(ScheduleModeratorInterface::class),
            $this->createMock(DateTime::class),
        ];
    }
}
