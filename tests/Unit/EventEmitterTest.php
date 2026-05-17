<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Unit;

use CommonPHP\Runtime\Contracts\EventInterface;
use CommonPHP\Runtime\Support\EventEmitter;
use CommonPHP\Runtime\Tests\Fixtures\Events\SampleEvent;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EventEmitterTest extends TestCase
{
    public function testEnsureEventRegistersEventWithoutSubscribers(): void
    {
        $emitter = new EventEmitter();

        $emitter->ensureEvent(SampleEvent::class);

        self::assertFalse($emitter->hasSubscribers(SampleEvent::class));
        self::assertInstanceOf(SampleEvent::class, $emitter->emit(new SampleEvent()));
    }

    public function testSubscribersRunByPriorityAndRegistrationOrder(): void
    {
        $emitter = new EventEmitter();
        $calls = [];

        $emitter
            ->subscribe(SampleEvent::class, static function () use (&$calls): void {
                $calls[] = 'middle';
            })
            ->subscribe(SampleEvent::class, static function () use (&$calls): void {
                $calls[] = 'first';
            }, 10)
            ->subscribe(SampleEvent::class, static function () use (&$calls): void {
                $calls[] = 'last';
            }, -10)
            ->subscribe(SampleEvent::class, static function () use (&$calls): void {
                $calls[] = 'second';
            }, 10);

        $emitter->emit(new SampleEvent());

        self::assertSame(['first', 'second', 'middle', 'last'], $calls);
    }

    public function testInvalidEventClassesAreRejected(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not implement ' . EventInterface::class);

        new EventEmitter()->ensureEvent(self::class);
    }
}
