<?php

declare(strict_types=1);

namespace Kreait\Firebase\DynamicLink;

use JsonSerializable;
use Kreait\Firebase\Value\Url;
use Stringable;

/**
 * @phpstan-type ShortenLongDynamicLinkShape array{
 *     longDynamicLink: non-empty-string,
 *     suffix: array{
 *         option: self::WITH*
 *     }
 * }
 */
final class ShortenLongDynamicLink implements JsonSerializable
{
    /**
     * @var ShortenLongDynamicLinkShape
     * @readonly
     */
    private array $data;
    public const WITH_UNGUESSABLE_SUFFIX = 'UNGUESSABLE';
    public const WITH_SHORT_SUFFIX = 'SHORT';

    /**
     * @param ShortenLongDynamicLinkShape $data
     */
    private function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * The long dynamic link that has been created as described in {@see https://firebase.google.com/docs/dynamic-links/create-manually}.
     * @param Stringable|string $url
     */
    public static function forLongDynamicLink($url): self
    {
        return new self([
            'longDynamicLink' => Url::fromString($url)->value,
            'suffix' => ['option' => self::WITH_UNGUESSABLE_SUFFIX],
        ]);
    }

    /**
     * @param ShortenLongDynamicLinkShape $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function withUnguessableSuffix(): self
    {
        $data = $this->data;
        $data['suffix']['option'] = self::WITH_UNGUESSABLE_SUFFIX;

        return new self($data);
    }

    public function withShortSuffix(): self
    {
        $data = $this->data;
        $data['suffix']['option'] = self::WITH_SHORT_SUFFIX;

        return new self($data);
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
