<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Beste\Json;
use Iterator;
use Kreait\Firebase\Messaging\RegistrationToken;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RegistrationTokenTest extends TestCase
{
    public function fromValue(string $expected, string $value): void
    {
        $token = RegistrationToken::fromValue($value);
        $this->assertSame($expected, $token->value());
        $this->assertSame('"'.$token.'"', Json::encode($token));
    }

    public static function valueProvider(): Iterator
    {
        yield 'foo' => ['foo', 'foo'];
    }
}
