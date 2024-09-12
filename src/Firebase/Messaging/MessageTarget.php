<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use Kreait\Firebase\Exception\InvalidArgumentException;

use function mb_strtolower;

final class MessageTarget
{
    /**
     * @var non-empty-string
     * @readonly
     */
    private string $type;
    /**
     * @var non-empty-string
     * @readonly
     */
    private string $value;
    public const CONDITION = 'condition';
    public const TOKEN = 'token';
    public const TOPIC = 'topic';

    /**
     * @internal
     */
    public const UNKNOWN = 'unknown';
    public const TYPES = [
        self::CONDITION, self::TOKEN, self::TOPIC, self::UNKNOWN,
    ];

    /**
     * @param non-empty-string $type
     * @param non-empty-string $value
     */
    private function __construct(string $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Create a new message target with the given type and value.
     *
     * @param self::CONDITION|self::TOKEN|self::TOPIC|self::UNKNOWN $type
     * @param non-empty-string $value
     *
     * @throws InvalidArgumentException
     */
    public static function with(string $type, string $value): self
    {
        $targetType = mb_strtolower($type);

        switch ($targetType) {
            case self::CONDITION:
                $targetValue = Condition::fromValue($value)->value();
                break;
            case self::TOKEN:
                $targetValue = RegistrationToken::fromValue($value)->value();
                break;
            case self::TOPIC:
                $targetValue = Topic::fromValue($value)->value();
                break;
            case self::UNKNOWN:
                $targetValue = $value;
                break;
        }

        return new self($targetType, $targetValue);
    }

    /**
     * @return non-empty-string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return non-empty-string
     */
    public function value(): string
    {
        return $this->value;
    }
}
