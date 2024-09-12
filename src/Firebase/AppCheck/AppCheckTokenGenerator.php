<?php

declare(strict_types=1);

namespace Kreait\Firebase\AppCheck;

use Beste\Clock\SystemClock;
use Firebase\JWT\JWT;
use Kreait\Firebase\Exception\AppCheck\InvalidAppCheckTokenOptions;
use Psr\Clock\ClockInterface;

/**
 * @internal
 */
final class AppCheckTokenGenerator
{
    /**
     * @var non-empty-string
     * @readonly
     */
    private string $clientEmail;
    /**
     * @var non-empty-string
     * @readonly
     */
    private string $privateKey;
    private const APP_CHECK_AUDIENCE = 'https://firebaseappcheck.googleapis.com/google.firebase.appcheck.v1.TokenExchangeService';
    /**
     * @readonly
     */
    private ClockInterface $clock;

    /**
     * @param non-empty-string $clientEmail
     * @param non-empty-string $privateKey
     */
    public function __construct(string $clientEmail, string $privateKey, ?ClockInterface $clock = null)
    {
        $this->clientEmail = $clientEmail;
        $this->privateKey = $privateKey;
        $this->clock = $clock ?? SystemClock::create();
    }

    /**
     * @param non-empty-string $appId the Application ID to use for the generated token
     *
     * @throws InvalidAppCheckTokenOptions
     *
     * @return string the generated token
     */
    public function createCustomToken(string $appId, ?AppCheckTokenOptions $options = null): string
    {
        $now = $this->clock->now()->getTimestamp();
        $payload = [
            'iss' => $this->clientEmail,
            'sub' => $this->clientEmail,
            'app_id' => $appId,
            'aud' => self::APP_CHECK_AUDIENCE,
            'iat' => $now,
            'exp' => $now + 300,
        ];

        if (null !== $options && $options->ttl) {
            $payload['ttl'] = $options->ttl.'s';
        }

        return JWT::encode($payload, $this->privateKey, 'RS256');
    }
}
