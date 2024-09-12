<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\AppCheck;

use Iterator;
use Kreait\Firebase\AppCheck\AppCheckTokenOptions;
use Kreait\Firebase\Exception\AppCheck\InvalidAppCheckTokenOptions;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class AppCheckTokenOptionsTest extends UnitTestCase
{
    public function fromArrayWithOmittedOptions(): void
    {
        $options = AppCheckTokenOptions::fromArray([]);
        $this->assertNull($options->ttl);
    }

    public function fromArrayWithValidOptions(?int $ttl): void
    {
        $options = AppCheckTokenOptions::fromArray([
            'ttl' => $ttl,
        ]);
        $this->assertSame($ttl, $options->ttl);
    }

    public function fromArrayWithInvalidOptions(int $ttl): void
    {
        $this->expectException(InvalidAppCheckTokenOptions::class);
        AppCheckTokenOptions::fromArray([
            'ttl' => $ttl,
        ]);
    }

    public static function validOptions(): Iterator
    {
        yield 'null' => [null];
        yield 'min-boundary' => [1800];
        yield 'mid-range' => [30240];
        yield 'max-boundary' => [60480];
    }

    public static function invalidOptions(): Iterator
    {
        yield 'too-small' => [1799];
        yield 'too-large' => [604801];
    }
}
