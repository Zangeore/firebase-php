<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use JsonSerializable;

/**
 * @phpstan-type RemoteConfigPersonalizationValueShape array{
 *    personalizationId: string
 * }
 *
 * @see https://firebase.google.com/docs/reference/remote-config/rest/v1/RemoteConfig#personalizationvalue
 */
final class PersonalizationValue implements JsonSerializable
{
    /**
     * @var RemoteConfigPersonalizationValueShape
     * @readonly
     */
    private array $data;
    /**
     * @param RemoteConfigPersonalizationValueShape $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param RemoteConfigPersonalizationValueShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * @return RemoteConfigPersonalizationValueShape
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return RemoteConfigPersonalizationValueShape
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
