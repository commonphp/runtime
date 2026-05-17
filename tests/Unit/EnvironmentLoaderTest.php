<?php

declare(strict_types=1);

namespace CommonPHP\Runtime\Tests\Unit;

use CommonPHP\Runtime\Support\EnvironmentLoader;
use CommonPHP\Runtime\Support\EnvironmentState;
use CommonPHP\Runtime\Support\NativePathResolver;
use PHPUnit\Framework\TestCase;

final class EnvironmentLoaderTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $serverSnapshot = [];

    /**
     * @var array<string, mixed>
     */
    private array $envSnapshot = [];

    private string|false $originalAppEnv = false;

    private string|false $originalAppDebug = false;

    private ?string $tempRoot = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverSnapshot = $_SERVER;
        $this->envSnapshot = $_ENV;
        $this->originalAppEnv = getenv('APP_ENV');
        $this->originalAppDebug = getenv('APP_DEBUG');
        $this->tempRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'comphp-runtime-' . bin2hex(random_bytes(6));

        mkdir($this->tempRoot);
        $this->clearAppEnvironment();
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverSnapshot;
        $_ENV = $this->envSnapshot;
        $this->restoreEnvironmentVariable('APP_ENV', $this->originalAppEnv);
        $this->restoreEnvironmentVariable('APP_DEBUG', $this->originalAppDebug);

        if ($this->tempRoot !== null) {
            $this->removeDirectory($this->tempRoot);
        }

        parent::tearDown();
    }

    public function testExistingStateIsPreservedWhenNoEnvironmentSourcesExist(): void
    {
        $state = new EnvironmentState('preview', true);

        new EnvironmentLoader()->load(new NativePathResolver($this->tempRoot), $state);

        self::assertSame('preview', $state->getEnvironment());
        self::assertTrue($state->isDebugging());
    }

    public function testServerEnvironmentOverridesExistingState(): void
    {
        $_SERVER['APP_ENV'] = 'qa';
        $_SERVER['APP_DEBUG'] = '0';
        $state = new EnvironmentState('preview', true);

        new EnvironmentLoader()->load(new NativePathResolver($this->tempRoot), $state);

        self::assertSame('qa', $state->getEnvironment());
        self::assertFalse($state->isDebugging());
    }

    public function testDotenvFileIsLoadedWhenPresent(): void
    {
        file_put_contents($this->tempRoot . DIRECTORY_SEPARATOR . '.env', "APP_ENV=dotenv\nAPP_DEBUG=1\n");
        $state = new EnvironmentState('prod', false);

        new EnvironmentLoader()->load(new NativePathResolver($this->tempRoot), $state);

        self::assertSame('dotenv', $state->getEnvironment());
        self::assertTrue($state->isDebugging());
    }

    private function clearAppEnvironment(): void
    {
        unset($_SERVER['APP_ENV'], $_SERVER['APP_DEBUG'], $_ENV['APP_ENV'], $_ENV['APP_DEBUG']);
        putenv('APP_ENV');
        putenv('APP_DEBUG');
    }

    private function restoreEnvironmentVariable(string $name, string|false $value): void
    {
        if ($value === false) {
            putenv($name);

            return;
        }

        putenv($name . '=' . $value);
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);

        foreach ($items === false ? [] : $items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->removeDirectory($path);
                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
