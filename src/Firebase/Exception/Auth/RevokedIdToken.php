<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Auth;

use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\RuntimeException;
use Lcobucci\JWT\Token;
use Throwable;

final class RevokedIdToken extends RuntimeException implements AuthException
{
    /**
     * @readonly
     */
    private Token $token;
    public function __construct(Token $token, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->token = $token;
        $message = $message ?: 'The Firebase ID token has been revoked.';
        parent::__construct($message, $code, $previous);
    }
    public function getToken(): Token
    {
        return $this->token;
    }
}
