<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Csrf;
use PHPUnit\Framework\TestCase;

final class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testTokenIsReusedForTheSession(): void
    {
        $first = Csrf::token();
        $second = Csrf::token();

        self::assertSame($first, $second);
        self::assertSame(64, strlen($first));
    }

    public function testValidTokenVerifies(): void
    {
        $token = Csrf::token();

        self::assertTrue(Csrf::verify($token));
    }

    public function testInvalidTokenIsRejected(): void
    {
        Csrf::token();

        self::assertFalse(Csrf::verify('not-the-right-token'));
        self::assertFalse(Csrf::verify(null));
        self::assertFalse(Csrf::verify(''));
    }

    public function testFieldContainsEscapedToken(): void
    {
        $token = Csrf::token();
        $field = Csrf::field();

        self::assertStringContainsString('name="_token"', $field);
        self::assertStringContainsString($token, $field);
    }
}
