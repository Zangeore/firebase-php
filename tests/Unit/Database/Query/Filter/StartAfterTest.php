<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Iterator;
use Kreait\Firebase\Database\Query\Filter\StartAfter;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class StartAfterTest extends UnitTestCase
{
    /**
     * @param mixed $given
     * @param mixed $expected
     */
    public function modifyUri($given, string $expected): void
    {
        $filter = new StartAfter($given);
        $this->assertStringContainsString($expected, (string) $filter->modifyUri(new Uri('http://example.com')));
    }

    public static function valueProvider(): Iterator
    {
        yield 'int' => [1, 'startAfter=1'];
        yield 'string' => ['value', 'startAfter=%22value%22'];
    }
}
