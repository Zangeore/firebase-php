<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Filter;

use GuzzleHttp\Psr7\Uri;
use Iterator;
use Kreait\Firebase\Database\Query\Filter\EndAt;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class EndAtTest extends UnitTestCase
{
    /**
     * @param mixed $given
     */
    public function modifyUri($given, string $expected): void
    {
        $filter = new EndAt($given);
        $this->assertStringContainsString($expected, (string) $filter->modifyUri(new Uri('http://example.com')));
    }

    public static function valueProvider(): Iterator
    {
        yield 'int' => [1, 'endAt=1'];
        yield 'string' => ['value', 'endAt=%22value%22'];
    }
}
