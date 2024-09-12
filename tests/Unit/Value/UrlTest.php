<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Value;

use Iterator;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Value\Url;
use PHPUnit\Framework\TestCase;
use Stringable;

/**
 * @internal
 */
final class UrlTest extends TestCase
{
    /**
     * @param Stringable|string $value
     */
    public function withValidValue($value): void
    {
        $url = Url::fromString($value)->value;
        $check = (string) $value;
        $this->assertSame($check, $url);
    }

    public function withInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        Url::fromString($value);
    }

    public static function validValues(): Iterator
    {
        yield 'string' => ['https://example.com'];
    }

    public static function invalidValues(): Iterator
    {
        yield 'https:///example.com' => ['https:///example.com'];
        yield 'http://:80' => ['http://:80'];
        yield '(empty)' => [''];
    }
}
