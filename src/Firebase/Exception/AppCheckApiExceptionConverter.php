<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use GuzzleHttp\Exception\RequestException;
use Kreait\Firebase\Exception\AppCheck\ApiConnectionFailed;
use Kreait\Firebase\Exception\AppCheck\AppCheckError;
use Kreait\Firebase\Exception\AppCheck\PermissionDenied;
use Kreait\Firebase\Http\ErrorResponseParser;
use Psr\Http\Client\NetworkExceptionInterface;
use Throwable;

/**
 * @internal
 */
final class AppCheckApiExceptionConverter
{
    /**
     * @readonly
     */
    private ErrorResponseParser $responseParser;

    public function __construct()
    {
        $this->responseParser = new ErrorResponseParser();
    }

    public function convertException(Throwable $exception): AppCheckException
    {
        if ($exception instanceof RequestException) {
            return $this->convertGuzzleRequestException($exception);
        }

        if ($exception instanceof NetworkExceptionInterface) {
            return new ApiConnectionFailed('Unable to connect to the API: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        return new AppCheckError($exception->getMessage(), $exception->getCode(), $exception);
    }

    private function convertGuzzleRequestException(RequestException $e): AppCheckException
    {
        $message = $e->getMessage();
        $code = $e->getCode();
        $response = $e->getResponse();

        if ($response !== null) {
            $message = $this->responseParser->getErrorReasonFromResponse($response);
            $code = $response->getStatusCode();
        }

        switch ($code) {
            case StatusCode::STATUS_UNAUTHORIZED:
            case StatusCode::STATUS_FORBIDDEN:
                return new PermissionDenied($message, $code, $e);
            default:
                return new AppCheckError($message, $code, $e);
        }
    }
}
