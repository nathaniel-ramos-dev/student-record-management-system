<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testInitialsForTwoWordName(): void
    {
        $user = new User(1, 'System Administrator', 'admin', 'a@b.c', 'hash', 'admin');

        self::assertSame('SA', $user->initials());
    }

    public function testInitialsForSingleWordName(): void
    {
        $user = new User(2, 'Registrar', 'staff', 's@b.c', 'hash', 'staff');

        self::assertSame('R', $user->initials());
    }

    public function testInitialsTrimsExtraWhitespace(): void
    {
        $user = new User(3, '  Maria   Santos   Dela Cruz  ', 'maria', 'm@b.c', 'hash', 'staff');

        // First and last whitespace-separated tokens: Maria + Cruz.
        self::assertSame('MC', $user->initials());
    }
}
