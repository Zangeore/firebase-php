<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Util;

use function http_build_query;
use function in_array;
use function preg_match;
use function rtrim;
use function strtr;
use function trim;

/**
 * @internal
 */
final class UrlBuilder
{
    /**
     * @var http|https
     * @readonly
     */
    private string $scheme;
    /**
     * @var non-empty-string
     * @readonly
     */
    private string $host;
    /**
     * @var array<string, string>
     * @readonly
     */
    private array $defaultQueryParams;
    private const EXPECTED_URL_FORMAT = '@^https://(?P<namespace>[^.]+)\.(?P<host>.+)$@';

    /**
     * @param 'http'|'https' $scheme
     * @param non-empty-string $host
     * @param array<string, string> $defaultQueryParams
     */
    private function __construct(string $scheme, string $host, array $defaultQueryParams)
    {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->defaultQueryParams = $defaultQueryParams;
    }

    /**
     * @param non-empty-string $databaseUrl
     */
    public static function create(string $databaseUrl): self
    {
        ['scheme' => $scheme, 'host' => $host, 'query' => $query] = self::parseDatabaseUrl($databaseUrl);

        return new self($scheme, $host, $query);
    }

    /**
     * @param array<string, string> $queryParams
     */
    public function getUrl(string $path, array $queryParams = []): string
    {
        $allQueryParams = $this->defaultQueryParams + $queryParams;
        $path = '/'.trim($path, '/');

        $url = strtr('{scheme}://{host}{path}?{queryParams}', [
            '{scheme}' => $this->scheme,
            '{host}' => $this->host,
            '{path}' => $path,
            '{queryParams}' => http_build_query($allQueryParams),
        ]);

        // If no queryParams are present, remove the trailing '?'
        return trim($url, '?');
    }

    /**
     * @param non-empty-string $databaseUrl
     *
     * @return array{
     *     scheme: 'http'|'https',
     *     host: non-empty-string,
     *     query: array<non-empty-string, non-empty-string>
     * }
     */
    private static function parseDatabaseUrl(string $databaseUrl): array
    {
        $databaseUrl = rtrim($databaseUrl, '/');

        if (preg_match(self::EXPECTED_URL_FORMAT, $databaseUrl, $matches) !== 1) {
            throw new InvalidArgumentException('Unexpected database URL format "'.$databaseUrl.'"');
        }

        $namespace = $matches['namespace'];
        $host = $matches['host'];

        $emulatorHost = Util::rtdbEmulatorHost();

        if (!in_array($emulatorHost, ['', '0', null], true)) {
            return [
                'scheme' => 'http',
                'host' => $emulatorHost,
                'query' => ['ns' => $namespace],
            ];
        }

        return [
            'scheme' => 'https',
            'host' => $namespace.'.'.$host,
            'query' => [],
        ];
    }
}
