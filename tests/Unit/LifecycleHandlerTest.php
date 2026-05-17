<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Unit;

use CommonPHP\Runtime\Contracts\AppInterface;
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Contracts\LifecycleInterface;
use CommonPHP\Runtime\Contracts\ServiceProviderInterface;
use CommonPHP\Runtime\Events\KernelStartedEvent;
use CommonPHP\Runtime\Events\KernelStartingEvent;
use CommonPHP\Runtime\Events\KernelStoppedEvent;
use CommonPHP\Runtime\Events\KernelStoppingEvent;
use CommonPHP\Runtime\Support\AppContext;
use CommonPHP\Runtime\Support\EventEmitter;
use CommonPHP\Runtime\Support\ExitStatus;
use CommonPHP\Runtime\Support\LifecycleHandler;
use DateTimeImmutable;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class LifecycleHandlerTest extends TestCase
{
    public function testStartupAndShutdownRunParticipantsInDocumentedOrder(): void
    {
        $recorder = new LifecycleRecorder();
        $app = new LifecycleTestApp($recorder);
        $executive = new LifecycleTestExecutive($recorder);
        $moduleA = new LifecycleTestModule($recorder, 'module-a');
        $moduleB = new LifecycleTestModule($recorder, 'module-b');
        $provider = new LifecycleTestProvider($recorder);
        $events = new EventEmitter();
        $exception = new RuntimeException('boom');

        $events
            ->subscribe(KernelStartingEvent::class, static fn () => $recorder->add('event:starting'))
            ->subscribe(KernelStartedEvent::class, static fn () => $recorder->add('event:started'))
            ->subscribe(
                KernelStoppingEvent::class,
                static fn (KernelStoppingEvent $event) => $recorder->add(
                    'event:stopping:' . $event->exitCode . ':' . $event->exception?->getMessage(),
                ),
            )
            ->subscribe(KernelStoppedEvent::class, static fn () => $recorder->add('event:stopped'));

        $handler = new LifecycleHandler();

        $handler->startup($app, $executive, [$moduleA, $moduleB], [$provider], $events);
        $handler->shutdown($app, $executive, [$moduleA, $moduleB], [$provider], $events, 7, $exception);

        self::assertSame([
            'event:starting',
            'app:startup',
            'executive:startup',
            'module-a:startup',
            'module-b:startup',
            'provider:startup',
            'event:started',
            'event:stopping:7:boom',
            'provider:shutdown',
            'module-b:shutdown',
            'module-a:shutdown',
            'executive:shutdown',
            'app:shutdown',
            'event:stopped',
        ], $recorder->calls);
    }
}

final class LifecycleRecorder
{
    /**
     * @var list<string>
     */
    public array $calls = [];

    public function add(string $call): void
    {
        $this->calls[] = $call;
    }
}

final class LifecycleTestApp implements AppInterface, LifecycleInterface
{
    public function __construct(
        private readonly LifecycleRecorder $recorder,
    ) {
    }

    public function getStartedAt(): DateTimeImmutable
    {
        return new DateTimeImmutable('@0');
    }

    public function getEnvironment(): string
    {
        return 'test';
    }

    public function isDebugging(): bool
    {
        return true;
    }

    public function getContext(): AppContext
    {
        return new AppContext($this->getStartedAt(), $this->getEnvironment(), $this->isDebugging(), __DIR__);
    }

    public function startup(): void
    {
        $this->recorder->add('app:startup');
    }

    public function shutdown(): void
    {
        $this->recorder->add('app:shutdown');
    }
}

final class LifecycleTestExecutive implements ExecutiveInterface, LifecycleInterface
{
    public function __construct(
        private readonly LifecycleRecorder $recorder,
    ) {
    }

    public function execute(): int
    {
        return ExitStatus::SUCCESS;
    }

    public function startup(): void
    {
        $this->recorder->add('executive:startup');
    }

    public function shutdown(): void
    {
        $this->recorder->add('executive:shutdown');
    }
}

final class LifecycleTestModule implements LifecycleInterface
{
    public function __construct(
        private readonly LifecycleRecorder $recorder,
        private readonly string $name,
    ) {
    }

    public function startup(): void
    {
        $this->recorder->add($this->name . ':startup');
    }

    public function shutdown(): void
    {
        $this->recorder->add($this->name . ':shutdown');
    }
}

final class LifecycleTestProvider implements ServiceProviderInterface, LifecycleInterface
{
    public function __construct(
        private readonly LifecycleRecorder $recorder,
    ) {
    }

    public function configure(ContainerBuilder $builder): void
    {
    }

    public function startup(): void
    {
        $this->recorder->add('provider:startup');
    }

    public function shutdown(): void
    {
        $this->recorder->add('provider:shutdown');
    }
}
