<?php

declare(strict_types=1);

namespace Kreait\Firebase\Database\Query\Filter;

use Beste\Json;
use Kreait\Firebase\Database\Query\Filter;
use Kreait\Firebase\Database\Query\ModifierTrait;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 */
final class StartAfter implements Filter
{
    /**
     * @readonly
     * @var bool|float|int|string
     */
    private $value;
    use ModifierTrait;

    /**
     * @param int|float|string|bool $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function modifyUri(UriInterface $uri): UriInterface
    {
        return $this->appendQueryParam($uri, 'startAfter', Json::encode($this->value));
    }
}
