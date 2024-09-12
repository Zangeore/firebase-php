<?php

declare(strict_types=1);

namespace Kreait\Firebase\Auth;

use DateTimeImmutable;
use Kreait\Firebase\Util\DT;

use function array_key_exists;

/**
 * @phpstan-type MfaInfoResponseShape array{
 *     mfaEnrollmentId: non-empty-string,
 *     displayName?: non-empty-string,
 *     phoneInfo?: non-empty-string,
 *     enrolledAt?: non-empty-string
 * }
 */
final class MfaInfo
{
    /**
     * @readonly
     */
    public string $mfaEnrollmentId;
    /**
     * @readonly
     */
    public ?string $displayName;
    /**
     * @readonly
     */
    public ?string $phoneInfo;
    /**
     * @readonly
     */
    public ?DateTimeImmutable $enrolledAt;
    private function __construct(string $mfaEnrollmentId, ?string $displayName, ?string $phoneInfo, ?DateTimeImmutable $enrolledAt)
    {
        $this->mfaEnrollmentId = $mfaEnrollmentId;
        $this->displayName = $displayName;
        $this->phoneInfo = $phoneInfo;
        $this->enrolledAt = $enrolledAt;
    }
    /**
     * @internal
     *
     * @param MfaInfoResponseShape $data
     */
    public static function fromResponseData(array $data): self
    {
        $enrolledAt = array_key_exists('enrolledAt', $data)
            ? DT::toUTCDateTimeImmutable($data['enrolledAt'])
            : null;

        return new self(
            $data['mfaEnrollmentId'],
            $data['displayName'] ?? null,
            $data['phoneInfo'] ?? null,
            $enrolledAt,
        );
    }
}
