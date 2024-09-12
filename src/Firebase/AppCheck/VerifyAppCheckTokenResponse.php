<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

/**
 * @phpstan-import-type DecodedAppCheckTokenShape from DecodedAppCheckToken
 *
 * @phpstan-type VerifyAppCheckTokenResponseShape array{
 *     appId: non-empty-string,
 *     token: DecodedAppCheckTokenShape,
 * }
 */
final class VerifyAppCheckTokenResponse
{
    /**
     * @var non-empty-string
     * @readonly
     */
    public string $appId;
    /**
     * @readonly
     */
    public DecodedAppCheckToken $token;
    /**
     * @param non-empty-string $appId
     */
    public function __construct(string $appId, DecodedAppCheckToken $token)
    {
        $this->appId = $appId;
        $this->token = $token;
    }
}
