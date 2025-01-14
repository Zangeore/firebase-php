<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;
use Kreait\Firebase\DynamicLink\AnalyticsInfo\GooglePlayAnalytics;
use Kreait\Firebase\DynamicLink\AnalyticsInfo\ITunesConnectAnalytics;

use function array_key_exists;

/**
 * @phpstan-import-type GooglePlayAnalyticsShape from GooglePlayAnalytics
 * @phpstan-import-type ITunesConnectAnalyticsShape from ITunesConnectAnalytics
 *
 * @phpstan-type AnalyticsInfoShape array{
 *     googlePlayAnalytics?: GooglePlayAnalyticsShape,
 *     itunesConnectAnalytics?: ITunesConnectAnalyticsShape
 * }
 */
final class AnalyticsInfo implements JsonSerializable
{
    /**
     * @readonly
     */
    private ?GooglePlayAnalytics $googlePlayAnalytics;
    /**
     * @readonly
     */
    private ?ITunesConnectAnalytics $iTunesConnectAnalytics;
    private function __construct(?GooglePlayAnalytics $googlePlayAnalytics, ?ITunesConnectAnalytics $iTunesConnectAnalytics)
    {
        $this->googlePlayAnalytics = $googlePlayAnalytics;
        $this->iTunesConnectAnalytics = $iTunesConnectAnalytics;
    }
    /**
     * @param AnalyticsInfoShape $data
     */
    public static function fromArray(array $data): self
    {
        $googlePlayAnalytics = array_key_exists('googlePlayAnalytics', $data)
            ? GooglePlayAnalytics::fromArray($data['googlePlayAnalytics'])
            : null;

        $itunesConnectAnalytics = array_key_exists('itunesConnectAnalytics', $data)
            ? ITunesConnectAnalytics::fromArray($data['itunesConnectAnalytics'])
            : null;

        return new self($googlePlayAnalytics, $itunesConnectAnalytics);
    }

    public static function new(): self
    {
        return new self(null, null);
    }

    /**
     * @param GooglePlayAnalytics|GooglePlayAnalyticsShape $data
     */
    public function withGooglePlayAnalyticsInfo($data): self
    {
        $gpInfo = $data instanceof GooglePlayAnalytics ? $data : GooglePlayAnalytics::fromArray($data);

        return new self($gpInfo, $this->iTunesConnectAnalytics);
    }

    /**
     * @param ITunesConnectAnalytics|ITunesConnectAnalyticsShape $data
     */
    public function withItunesConnectAnalytics($data): self
    {
        $icInfo = $data instanceof ITunesConnectAnalytics ? $data : ITunesConnectAnalytics::fromArray($data);

        return new self($this->googlePlayAnalytics, $icInfo);
    }

    /**
     * @return AnalyticsInfoShape
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'googlePlayAnalytics' => ($nullsafeVariable1 = $this->googlePlayAnalytics) ? $nullsafeVariable1->jsonSerialize() : null,
            'itunesConnectAnalytics' => ($nullsafeVariable2 = $this->iTunesConnectAnalytics) ? $nullsafeVariable2->jsonSerialize() : null,
        ]);
    }
}
