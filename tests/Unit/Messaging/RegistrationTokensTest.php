<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use InvalidArgumentException;
use Iterator;
use Kreait\Firebase\Messaging\RegistrationToken;
use Kreait\Firebase\Messaging\RegistrationTokens;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
final class RegistrationTokensTest extends TestCase
{
    /**
     * @param mixed $value
     */
    public function itCanBeCreatedFromValues(int $expectedCount, $value): void
    {
        $tokens = RegistrationTokens::fromValue($value);
        $this->assertCount($expectedCount, $tokens);
        $this->assertSame(!$expectedCount, $tokens->isEmpty());
    }

    /**
     * @param mixed $value
     */
    public function itRejectsInvalidValues($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        RegistrationTokens::fromValue($value);
    }

    public function itReturnsStrings(): void
    {
        $token = RegistrationToken::fromValue('foo');
        $tokens = RegistrationTokens::fromValue([$token, $token]);
        $this->assertEqualsCanonicalizing(['foo', 'foo'], $tokens->asStrings());
    }

    public static function validValuesWithExpectedCounts(): Iterator
    {
        $foo = RegistrationToken::fromValue('foo');
        yield 'string' => [1, 'foo'];
        yield 'token object' => [1, $foo];
        yield 'collection' => [2, new RegistrationTokens($foo, $foo)];
        yield 'array with mixed values' => [2, [$foo, 'bar']];
    }

    public static function invalidValues(): Iterator
    {
        yield 'invalid object' => [new stdClass()];
    }
}
