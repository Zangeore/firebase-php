<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

use function is_array;
use function is_string;

/**
 * @phpstan-import-type RemoteConfigParameterValueShape from ParameterValue
 */
class ConditionalValue implements JsonSerializable
{
    /**
     * @var non-empty-string
     * @readonly
     */
    private string $conditionName;
    /**
     * @readonly
     */
    private ParameterValue $value;
    /**
     * @internal
     *
     * @param non-empty-string $conditionName
     */
    public function __construct(string $conditionName, ParameterValue $value)
    {
        $this->conditionName = $conditionName;
        $this->value = $value;
    }

    /**
     * @return non-empty-string
     */
    public function conditionName(): string
    {
        return $this->conditionName;
    }

    /**
     * @param non-empty-string|Condition $condition
     */
    public static function basedOn($condition): self
    {
        $name = $condition instanceof Condition ? $condition->name() : $condition;

        return new self($name, ParameterValue::withValue(''));
    }

    /**
     * @return RemoteConfigParameterValueShape|non-empty-string
     */
    public function value()
    {
        $data = $this->value->toArray();

        $valueString = $data['value'] ?? null;

        if (is_string($valueString) && $valueString !== '') {
            return $valueString;
        }

        return $data;
    }

    /**
     * @param ParameterValue|RemoteConfigParameterValueShape|string $value
     */
    public function withValue($value): self
    {
        if (is_string($value)) {
            return new self($this->conditionName, ParameterValue::withValue($value));
        }

        if (is_array($value)) {
            return new self($this->conditionName, ParameterValue::fromArray($value));
        }

        return new self($this->conditionName, $value);
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
