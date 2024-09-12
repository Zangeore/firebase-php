<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Iterator;
use Kreait\Firebase\Database\Query\Filter\StartAt;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class StartAtTest extends UnitTestCase
{
    /**
     * @param mixed $given
     */
    public function modifyUri($given, string $expected): void
    {
        $filter = new StartAt($given);
        $this->assertStringContainsString($expected, (string) $filter->modifyUri(new Uri('http://example.com')));
    }

    public static function valueProvider(): Iterator
    {
        yield 'int' => [1, 'startAt=1'];
        yield 'string' => ['value', 'startAt=%22value%22'];
    }
}
