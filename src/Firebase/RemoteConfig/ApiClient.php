<?php

declare(strict_types=1);

namespace Kreait\Firebase\RemoteConfig;

use Beste\Json;
use GuzzleHttp\ClientInterface;
use Kreait\Firebase\Exception\RemoteConfigApiExceptionConverter;
use Kreait\Firebase\Exception\RemoteConfigException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use function array_filter;

/**
 * @internal
 */
class ApiClient
{
    /**
     * @readonly
     */
    private ClientInterface $client;
    /**
     * @readonly
     */
    private RemoteConfigApiExceptionConverter $errorHandler;
    /**
     * @readonly
     */
    private string $baseUri;

    public function __construct(string $projectId, ClientInterface $client)
    {
        $this->client = $client;
        $this->baseUri = "https://firebaseremoteconfig.googleapis.com/v1/projects/{$projectId}/remoteConfig";
        $this->errorHandler = new RemoteConfigApiExceptionConverter();
    }

    /**
     * @see https://firebase.google.com/docs/reference/remote-config/rest/v1/projects/getRemoteConfig
     *
     * @throws RemoteConfigException
     * @param VersionNumber|int|string|null $versionNumber
     */
    public function getTemplate($versionNumber = null): ResponseInterface
    {
        return $this->requestApi('GET', 'remoteConfig', [
            'query' => array_filter([
                'version_number' => (string) $versionNumber,
            ]),
        ]);
    }

    /**
     * @throws RemoteConfigException
     */
    public function validateTemplate(Template $template): ResponseInterface
    {
        return $this->requestApi('PUT', 'remoteConfig', [
            'headers' => [
                'Content-Type' => 'application/json; UTF-8',
                'If-Match' => $template->etag(),
            ],
            'query' => [
                'validate_only' => 'true',
            ],
            'body' => Json::encode($template),
        ]);
    }

    /**
     * @throws RemoteConfigException
     */
    public function publishTemplate(Template $template): ResponseInterface
    {
        return $this->requestApi('PUT', 'remoteConfig', [
            'headers' => [
                'Content-Type' => 'application/json; UTF-8',
                'If-Match' => $template->etag(),
            ],
            'body' => Json::encode($template),
        ]);
    }

    /**
     * @see https://firebase.google.com/docs/reference/remote-config/rest/v1/projects.remoteConfig/listVersions
     *
     * @throws RemoteConfigException
     */
    public function listVersions(FindVersions $query, ?string $nextPageToken = null): ResponseInterface
    {
        $uri = $this->baseUri.':listVersions';

        $since = $query->since();
        $until = $query->until();
        $lastVersionNumber = $query->lastVersionNumber();
        $pageSize = $query->pageSize();

        $since = ($nullsafeVariable1 = $since) ? $nullsafeVariable1->format('Y-m-d\TH:i:s.v\Z') : null;
        $until = ($nullsafeVariable2 = $until) ? $nullsafeVariable2->format('Y-m-d\TH:i:s.v\Z') : null;
        $lastVersionNumber = $lastVersionNumber !== null ? (string) $lastVersionNumber : null;
        $pageSize = $pageSize ? (string) $pageSize : null;

        return $this->requestApi('GET', $uri, [
            'query' => array_filter([
                'startTime' => $since,
                'endTime' => $until,
                'endVersionNumber' => $lastVersionNumber,
                'pageSize' => $pageSize,
                'pageToken' => $nextPageToken,
            ]),
        ]);
    }

    /**
     * @throws RemoteConfigException
     */
    public function rollbackToVersion(VersionNumber $versionNumber): ResponseInterface
    {
        $uri = $this->baseUri.':rollback';

        return $this->requestApi('POST', $uri, [
            'json' => [
                'version_number' => (string) $versionNumber,
            ],
        ]);
    }

    /**
     * @param non-empty-string $method
     * @param non-empty-string $uri
     * @param array<string, mixed>|null $options
     *
     * @throws RemoteConfigException
     */
    private function requestApi(string $method, string $uri, ?array $options = null): ResponseInterface
    {
        $options ??= [];
        $options['decode_content'] = 'gzip';

        try {
            return $this->client->request($method, $uri, $options);
        } catch (Throwable $e) {
            throw $this->errorHandler->convertException($e);
        }
    }
}
