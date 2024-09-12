<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

/**
 * @phpstan-import-type RemoteConfigParameterValueShape from ParameterValue
 *
 * @todo Deprecate/Remove in 8.0
 *
 * @see ParameterValue
 */
class DefaultValue implements JsonSerializable
{
    /**
     * @readonly
     */
    private ParameterValue $value;
    private function __construct(ParameterValue $value)
    {
        $this->value = $value;
    }

    public static function useInAppDefault(): self
    {
        return new self(ParameterValue::inAppDefault());
    }

    public static function with(string $value): self
    {
        return new self(ParameterValue::withValue($value));
    }

    /**
     * @param RemoteConfigParameterValueShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self(ParameterValue::fromArray($data));
    }

    /**
     * @return RemoteConfigParameterValueShape
     */
    public function toArray(): array
    {
        return $this->value->toArray();
    }

    /**
     * @return RemoteConfigParameterValueShape
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
