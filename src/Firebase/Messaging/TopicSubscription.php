<?php

declare(strict_types=1);

namespace Kreait\Firebase\Messaging;

use DateTimeImmutable;
use JsonSerializable;

use const DATE_ATOM;

final class TopicSubscription implements JsonSerializable
{
    /**
     * @readonly
     */
    private Topic $topic;
    /**
     * @readonly
     */
    private RegistrationToken $registrationToken;
    /**
     * @readonly
     */
    private DateTimeImmutable $subscribedAt;
    public function __construct(Topic $topic, RegistrationToken $registrationToken, DateTimeImmutable $subscribedAt)
    {
        $this->topic = $topic;
        $this->registrationToken = $registrationToken;
        $this->subscribedAt = $subscribedAt;
    }
    public function topic(): Topic
    {
        return $this->topic;
    }

    public function registrationToken(): RegistrationToken
    {
        return $this->registrationToken;
    }

    public function subscribedAt(): DateTimeImmutable
    {
        return $this->subscribedAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'topic' => $this->topic->value(),
            'registration_token' => $this->registrationToken->value(),
            'subscribed_at' => $this->subscribedAt->format(DATE_ATOM),
        ];
    }
}
