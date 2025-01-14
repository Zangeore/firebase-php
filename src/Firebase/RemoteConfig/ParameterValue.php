<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

use function array_key_exists;

/**
 * @phpstan-import-type RemoteConfigPersonalizationValueShape from PersonalizationValue
 * @phpstan-import-type RemoteConfigRolloutValueShape from RolloutValue
 *
 * @phpstan-type RemoteConfigParameterValueShape array{
 *     value?: string,
 *     useInAppDefault?: bool,
 *     personalizationValue?: RemoteConfigPersonalizationValueShape,
 *     rolloutValue?: RemoteConfigRolloutValueShape
 * }
 *
 * @see https://firebase.google.com/docs/reference/remote-config/rest/v1/RemoteConfig#remoteconfigparametervalue
 */
final class ParameterValue implements JsonSerializable
{
    /**
     * @readonly
     */
    private ?string $value = null;
    /**
     * @readonly
     */
    private ?bool $useInAppDefault = null;
    /**
     * @readonly
     */
    private ?PersonalizationValue $personalizationValue = null;
    /**
     * @readonly
     */
    private ?RolloutValue $rolloutValue = null;
    private function __construct(?string $value = null, ?bool $useInAppDefault = null, ?PersonalizationValue $personalizationValue = null, ?RolloutValue $rolloutValue = null)
    {
        $this->value = $value;
        $this->useInAppDefault = $useInAppDefault;
        $this->personalizationValue = $personalizationValue;
        $this->rolloutValue = $rolloutValue;
    }
    public static function withValue(string $value): self
    {
        return new self($value);
    }

    public static function inAppDefault(): self
    {
        return new self(null, true);
    }

    public static function withPersonalizationValue(PersonalizationValue $value): self
    {
        return new self(null, null, $value);
    }

    public static function withRolloutValue(RolloutValue $value): self
    {
        return new self(null, null, null, $value);
    }

    /**
     * @param RemoteConfigParameterValueShape $data
     */
    public static function fromArray(array $data): self
    {
        if (array_key_exists('value', $data)) {
            return self::withValue($data['value']);
        }

        if (array_key_exists('useInAppDefault', $data)) {
            return self::inAppDefault();
        }

        if (array_key_exists('personalizationValue', $data)) {
            return self::withPersonalizationValue(PersonalizationValue::fromArray($data['personalizationValue']));
        }

        if (array_key_exists('rolloutValue', $data)) {
            return self::withRolloutValue(RolloutValue::fromArray($data['rolloutValue']));
        }

        return new self();
    }

    /**
     * @return RemoteConfigParameterValueShape
     */
    public function toArray(): array
    {
        if ($this->value !== null) {
            return ['value' => $this->value];
        }

        if ($this->useInAppDefault !== null) {
            return ['useInAppDefault' => $this->useInAppDefault];
        }

        if ($this->personalizationValue !== null) {
            return ['personalizationValue' => $this->personalizationValue->toArray()];
        }

        if ($this->rolloutValue !== null) {
            return ['rolloutValue' => $this->rolloutValue->toArray()];
        }

        return [];
    }

    /**
     * @return RemoteConfigParameterValueShape
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
