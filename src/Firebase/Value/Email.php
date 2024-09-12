<?php

declare(strict_types=1);

namespace Kreait\Firebase\Value;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Stringable;

use function filter_var;

use const FILTER_VALIDATE_EMAIL;

/**
 * @internal
 */
final class Email
{
    /**
     * @var non-empty-string
     * @readonly
     */
    public string $value;

    private function __construct(string $value)
    {
        if ($value === '' || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('The email address is invalid.');
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
