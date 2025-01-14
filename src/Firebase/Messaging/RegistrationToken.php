<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use JsonSerializable;

final class RegistrationToken implements JsonSerializable
{
    /**
     * @var non-empty-string
     * @readonly
     */
    private string $value;
    /**
     * @param non-empty-string $value
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }
    /**
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return $this->value;
    }
    /**
     * @param non-empty-string $value
     */
    public static function fromValue(string $value): self
    {
        return new self($value);
    }
    /**
     * @return non-empty-string
     */
    public function value(): string
    {
        return $this->value;
    }
    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
