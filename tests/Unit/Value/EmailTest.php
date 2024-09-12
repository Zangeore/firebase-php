<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Iterator;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Email;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EmailTest extends TestCase
{
    public function withValidValue(string $value): void
    {
        $email = Email::fromString($value)->value;
        $this->assertSame($value, $email);
    }

    public function withInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        Email::fromString($value);
    }

    public static function validValues(): Iterator
    {
        yield 'user@example.com' => ['user@example.com'];
    }

    public static function invalidValues(): Iterator
    {
        yield 'empty string' => [''];
        yield 'invalid' => ['invalid'];
    }
}
