<?php

declare(strict_types=1);

namespace Creatortsv\Scheduler\Tests\Registrar;

use Creatortsv\Scheduler\Registrar\ScheduleRegistrar;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ScheduleRegistrarTest extends TestCase
{
    public static function dataProvider(): array
    {
        return [
            ['0 * * * *'],
            ['0 0 * * *'],
            ['0 0 */2 * *'],
        ];
    }

    #[DataProvider('dataProvider')]
    public function testRegister(string $expectedExpr): void
    {
        $registrar = new ScheduleRegistrar($expectedExpr);
        $expression = $registrar->register(fn () => true);

        $this->assertSame($expectedExpr, $expression->getExpression());
        $this->assertCount(1, $registrar);
    }

    public function testRegisterWithDefaultExpr(): void
    {
        $registrar = new ScheduleRegistrar();
        $expression = $registrar->register(fn () => true);

        $this->assertSame('* * * * *', $expression->getExpression());
    }

    #[DataProvider('dataProvider')]
    public function testRegisterWithPassedDefaultExpr(string $expectedExpr): void
    {
        $registrar = new ScheduleRegistrar($expectedExpr);
        $expression = $registrar->register(fn () => true);

        $this->assertSame($expectedExpr, $expression->getExpression());
    }

    public function testCurrent(): void
    {
        $registrar = new ScheduleRegistrar();
        $registrar->register($job = fn () => true);

        $this->assertSame($job, $registrar->current());
    }

    public function testKey(): void
    {
        $registrar = new ScheduleRegistrar();

        $this->assertSame($registrar->register(fn () => true), $registrar->key());
    }

    public function testCount(): void
    {
        $registrar = new ScheduleRegistrar();

        $this->assertSame(0, $registrar->count());

        $registrar->register(fn () => true);

        $this->assertSame(1, $registrar->count());
    }
}
