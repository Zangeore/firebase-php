<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Stringable;

/**
 * @internal
 */
final class Url
{
    /**
     * @var non-empty-string
     * @readonly
     */
    public string $value;

    /**
     * @param non-empty-string $value
     */
    private function __construct(string $value)
    {
        $startsWithHttp = strncmp($value, 'https://', strlen('https://')) === 0 || strncmp($value, 'http://', strlen('http://')) === 0;
        $parsedValue = parse_url($value);

        if (!$startsWithHttp || $parsedValue === false) {
            throw new InvalidArgumentException('The URL is invalid.');
        }

        $this->value = $value;
    }

    /**
     * @param Stringable|string $value
     */
    public static function fromString($value): self
    {
        $value = (string) $value;

        if ($value === '') {
            throw new InvalidArgumentException('The URL cannot be empty.');
        }

        return new self($value);
    }
}
