<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Stringable;

use function mb_strlen;

/**
 * @internal
 */
final class Uid
{
    /**
     * @var non-empty-string
     * @readonly
     */
    public string $value;

    private function __construct(string $value)
    {
        if ($value === '' || mb_strlen($value) > 128) {
            throw new InvalidArgumentException('A uid must be a non-empty string with at most 128 characters.');
        }

        $this->value = $value;
    }

    /**
     * @param Stringable|string $value
     */
    public static function fromString($value): self
    {
        return new self((string) $value);
    }
}
