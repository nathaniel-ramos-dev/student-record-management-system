<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $serverBackup = [];

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
    }

    public function testRootPath(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/';

        self::assertSame('/', Router::currentPath());
    }

    public function testQueryStringIsIgnored(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/students/12?tab=grades';

        self::assertSame('/students/12', Router::currentPath());
    }

    public function testSubdirectoryIsStripped(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/srms/index.php';
        $_SERVER['REQUEST_URI'] = '/srms/students';

        self::assertSame('/students', Router::currentPath());
    }

    public function testIndexPhpFallbackIsStripped(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/srms/index.php';
        $_SERVER['REQUEST_URI'] = '/srms/index.php/students/3/edit';

        self::assertSame('/students/3/edit', Router::currentPath());
    }

    public function testTrailingSlashIsNormalised(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/students/';

        self::assertSame('/students', Router::currentPath());
    }
}
