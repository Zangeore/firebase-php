<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Iterator;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Uid;
use PHPUnit\Framework\TestCase;

use function str_repeat;

/**
 * @internal
 */
final class UidTest extends TestCase
{
    public function withValidValue(string $uid): void
    {
        $this->assertSame($uid, Uid::fromString($uid)->value);
    }

    public function withInvalidValue(string $uid): void
    {
        $this->expectException(InvalidArgumentException::class);
        Uid::fromString($uid);
    }

    public static function validValues(): Iterator
    {
        yield 'uid' => ['uid'];
    }

    public static function invalidValues(): Iterator
    {
        yield 'empty string' => [''];
        yield 'too long' => [str_repeat('x', 129)];
    }
}
