<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Iterator;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\ClearTextPassword;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ClearTextPasswordTest extends TestCase
{
    /**
     * @param mixed $value
     */
    public function withValidValue($value): void
    {
        $password = ClearTextPassword::fromString($value)->value;
        $this->assertSame($value, $password);
    }

    /**
     * @param mixed $value
     */
    public function withInvalidValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        ClearTextPassword::fromString($value);
    }

    public static function validValues(): Iterator
    {
        yield 'long enough' => ['long enough'];
    }

    public static function invalidValues(): Iterator
    {
        yield 'empty string' => [''];
        yield 'less than 6 chars' => ['short'];
    }
}
