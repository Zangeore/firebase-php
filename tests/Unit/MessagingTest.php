<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit;

use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Exception\MessagingApiExceptionConverter;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\ApiClient;
use Kreait\Firebase\Messaging\AppInstanceApiClient;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class MessagingTest extends UnitTestCase
{
    private Messaging $messaging;

    protected function setUp(): void
    {
        $messagingApi = $this->createMock(ApiClient::class);
        $appInstanceApi = $this->createMock(AppInstanceApiClient::class);
        $exceptionConverter = new MessagingApiExceptionConverter();

        $this->messaging = new Messaging($messagingApi, $appInstanceApi, $exceptionConverter);
    }

    public function sendInvalidArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->messaging->send([]);
    }

    public function subscribeToTopicWithEmptyTokenList(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->subscribeToTopic('topic', []);
    }

    public function unsubscribeFromTopicWithEmptyTokenList(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->messaging->unsubscribeFromTopic('topic', []);
    }

    public function itWillNotSendAMessageWithoutATarget(): void
    {
        $message = CloudMessage::new();
        $this->assertFalse($message->hasTarget());
        $this->expectException(InvalidArgumentException::class);
        $this->messaging->send($message);
    }
}
