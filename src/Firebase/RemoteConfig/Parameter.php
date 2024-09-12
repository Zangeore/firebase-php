<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

use function is_bool;
use function is_string;

/**
 * @phpstan-import-type RemoteConfigParameterValueShape from ParameterValue
 *
 * @phpstan-type RemoteConfigParameterShape array{
 *     description?: string|null,
 *     defaultValue?: RemoteConfigParameterValueShape|null,
 *     conditionalValues?: array<non-empty-string, RemoteConfigParameterValueShape>|null,
 *     valueType?: non-empty-string|null
 * }
 */
class Parameter implements JsonSerializable
{
    /**
     * @var non-empty-string
     * @readonly
     */
    private string $name;
    /**
     * @readonly
     */
    private string $description;
    /**
     * @readonly
     */
    private ?ParameterValue $defaultValue;
    /**
     * @var list<ConditionalValue>
     * @readonly
     */
    private array $conditionalValues;
    /**
     * @readonly
     */
    private ParameterValueType $valueType;
    /**
     * @param non-empty-string $name
     * @param list<ConditionalValue> $conditionalValues
     */
    private function __construct(string $name, string $description, ?ParameterValue $defaultValue, array $conditionalValues, ParameterValueType $valueType)
    {
        $this->name = $name;
        $this->description = $description;
        $this->defaultValue = $defaultValue;
        $this->conditionalValues = $conditionalValues;
        $this->valueType = $valueType;
    }
    /**
     * @param non-empty-string $name
     * @param DefaultValue|RemoteConfigParameterValueShape|string|bool|null $defaultValue
     * @param ?\Kreait\Firebase\RemoteConfig\ParameterValueType::* $valueType
     */
    public static function named(string $name, $defaultValue = null, ?function $valueType = null): self
    {
        $defaultValue = self::mapDefaultValue($defaultValue);

        return new self(
            $name,
            '',
            $defaultValue,
            [],
            $valueType ?? ParameterValueType::UNSPECIFIED,
        );
    }

    /**
     * @param DefaultValue|RemoteConfigParameterValueShape|string|bool|null $defaultValue
     */
    private static function mapDefaultValue($defaultValue): ?ParameterValue
    {
        if ($defaultValue === null) {
            return null;
        }

        if ($defaultValue instanceof DefaultValue) {
            return ParameterValue::fromArray($defaultValue->toArray());
        }

        if (is_string($defaultValue)) {
            return ParameterValue::withValue($defaultValue);
        }

        if (is_bool($defaultValue)) {
            return ParameterValue::inAppDefault();
        }

        return ParameterValue::fromArray($defaultValue);
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function withDescription(string $description): self
    {
        return new self(
            $this->name,
            $description,
            $this->defaultValue,
            $this->conditionalValues,
            $this->valueType,
        );
    }

    /**
     * @param DefaultValue|RemoteConfigParameterValueShape|string|bool|null $defaultValue
     */
    public function withDefaultValue($defaultValue): self
    {
        $defaultValue = self::mapDefaultValue($defaultValue);

        return new self(
            $this->name,
            $this->description,
            $defaultValue,
            $this->conditionalValues,
            $this->valueType,
        );
    }

    /**
     * @todo 8.0 Replace with `ParameterValue`
     */
    public function defaultValue(): ?DefaultValue
    {
        if ($this->defaultValue === null) {
            return null;
        }

        return DefaultValue::fromArray($this->defaultValue->toArray());
    }

    public function withConditionalValue(ConditionalValue $conditionalValue): self
    {
        $conditionalValues = $this->conditionalValues;
        $conditionalValues[] = $conditionalValue;

        return new self(
            $this->name,
            $this->description,
            $this->defaultValue,
            $conditionalValues,
            $this->valueType,
        );
    }

    /**
     * @return list<ConditionalValue>
     */
    public function conditionalValues(): array
    {
        return $this->conditionalValues;
    }

    /**
     * @param mixed $valueType
     */
    public function withValueType($valueType): self
    {
        return new self(
            $this->name,
            $this->description,
            $this->defaultValue,
            $this->conditionalValues,
            $valueType,
        );
    }

    public function valueType(): ParameterValueType
    {
        return $this->valueType;
    }

    /**
     * @return RemoteConfigParameterShape
     */
    public function toArray(): array
    {
        $conditionalValues = [];

        foreach ($this->conditionalValues() as $conditionalValue) {
            $conditionalValues[$conditionalValue->conditionName()] = $conditionalValue->toArray();
        }

        $array = [];

        if ($this->defaultValue !== null) {
            $array['defaultValue'] = $this->defaultValue->toArray();
        }

        if ($conditionalValues !== []) {
            $array['conditionalValues'] = $conditionalValues;
        }

        if ($this->description !== '') {
            $array['description'] = $this->description;
        }

        $array['valueType'] = $this->valueType->value;

        return $array;
    }

    /**
     * @return RemoteConfigParameterShape
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
