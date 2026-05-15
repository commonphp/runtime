<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Unit;

use CommonPHP\Runtime\Contracts\EventInterface;
use CommonPHP\Runtime\Tests\Fixtures\EventEmitterHarness;
use CommonPHP\Runtime\Tests\Fixtures\Events\SampleEvent;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EventEmitterTraitTest extends TestCase
{
    public function testSubscribersRunByPriorityAndThenRegistrationOrder(): void
    {
        $emitter = new EventEmitterHarness();
        $calls = [];

        $emitter
            ->subscribe(SampleEvent::class, static function (SampleEvent $event) use (&$calls): void {
                $calls[] = 'normal:' . $event->value;
            })
            ->subscribe(SampleEvent::class, static function () use (&$calls): void {
                $calls[] = 'high-first';
            }, 10)
            ->subscribe(SampleEvent::class, static function () use (&$calls): void {
                $calls[] = 'low';
            }, -10)
            ->subscribe(SampleEvent::class, static function () use (&$calls): void {
                $calls[] = 'high-second';
            }, 10);

        $event = new SampleEvent('payload');
        $emitted = $emitter->fire($event);

        self::assertSame($event, $emitted);
        self::assertTrue($emitter->hasSubscribers(SampleEvent::class));
        self::assertSame(['high-first', 'high-second', 'normal:payload', 'low'], $calls);
    }

    public function testEventListenerExceptionsBubbleToTheEmitterCaller(): void
    {
        $emitter = new EventEmitterHarness();
        $emitter->subscribe(
            SampleEvent::class,
            static function (): void {
                throw new RuntimeException('listener failed');
            },
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('listener failed');

        $emitter->fire(new SampleEvent());
    }

    public function testSubscribingToNonEventClassIsRejected(): void
    {
        $emitter = new EventEmitterHarness();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not implement ' . EventInterface::class);

        $emitter->subscribe(self::class, static function (): void {
        });
    }
}
