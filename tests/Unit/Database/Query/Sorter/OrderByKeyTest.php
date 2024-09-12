<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Database\Query\Sorter;

use GuzzleHttp\Psr7\Uri;
use Iterator;
use Kreait\Firebase\Database\Query\Sorter\OrderByKey;
use Kreait\Firebase\Tests\UnitTestCase;

use function rawurlencode;

/**
 * @internal
 */
final class OrderByKeyTest extends UnitTestCase
{
    private OrderByKey $sorter;

    protected function setUp(): void
    {
        $this->sorter = new OrderByKey();
    }

    public function modifyUri(): void
    {
        $this->assertStringContainsString(
            'orderBy='.rawurlencode('"$key"'),
            (string) $this->sorter->modifyUri(new Uri('http://example.com')),
        );
    }

    /**
     * @param mixed $expected
     * @param mixed $given
     */
    public function modifyValue($expected, $given): void
    {
        $this->assertSame($expected, $this->sorter->modifyValue($given));
    }

    public static function valueProvider(): Iterator
    {
        yield 'scalar' => [
            'expected' => 'scalar',
            'given' => 'scalar',
        ];
        yield 'array' => [
            'expected' => [
                'a' => 'any',
                'b' => 'any',
                'c' => 'any',
                'd' => 'any',
            ],
            'given' => [
                'c' => 'any',
                'a' => 'any',
                'd' => 'any',
                'b' => 'any',
            ],
        ];
    }
}
