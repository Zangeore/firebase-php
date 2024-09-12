<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

/**
 * @phpstan-type AppCheckTokenShape array{
 *     token: string,
 *     ttl: string
 * }
 */
final class AppCheckToken
{
    /**
     * @readonly
     */
    public string $token;
    /**
     * @readonly
     */
    public string $ttl;
    private function __construct(string $token, string $ttl)
    {
        $this->token = $token;
        $this->ttl = $ttl;
    }
    /**
     * @param AppCheckTokenShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['token'], $data['ttl']);
    }
}
