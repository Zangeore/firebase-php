<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

/**
 * @phpstan-type DecodedAppCheckTokenShape array{
 *     app_id?: non-empty-string,
 *     aud: array<string>,
 *     exp: int,
 *     iat: int,
 *     iss: non-empty-string,
 *     sub: non-empty-string,
 * }
 */
final class DecodedAppCheckToken
{
    /**
     * @var non-empty-string
     * @readonly
     */
    public string $app_id;
    /**
     * @var array<string>
     * @readonly
     */
    public array $aud;
    /**
     * @readonly
     */
    public int $exp;
    /**
     * @readonly
     */
    public int $iat;
    /**
     * @readonly
     */
    public string $iss;
    /**
     * @var non-empty-string
     * @readonly
     */
    public string $sub;
    /**
     * @param non-empty-string $app_id
     * @param array<string> $aud
     * @param non-empty-string $sub
     */
    private function __construct(string $app_id, array $aud, int $exp, int $iat, string $iss, string $sub)
    {
        $this->app_id = $app_id;
        $this->aud = $aud;
        $this->exp = $exp;
        $this->iat = $iat;
        $this->iss = $iss;
        $this->sub = $sub;
    }
    /**
     * @param DecodedAppCheckTokenShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['app_id'] ?? $data['sub'],
            $data['aud'],
            $data['exp'],
            $data['iat'],
            $data['iss'],
            $data['sub'],
        );
    }
}
