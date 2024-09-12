<?php

declare(strict_types=1);

namespace Kreait\Firebase\Tests\Unit\Messaging;

use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Tests\UnitTestCase;

/**
 * @internal
 */
final class NotificationTest extends UnitTestCase
{
    public function createWithEmptyStrings(): void
    {
        $notification = Notification::create('', '', '');
        $this->assertSame('', $notification->title());
        $this->assertSame('', $notification->body());
        $this->assertSame('', $notification->imageUrl());
        $this->assertEqualsCanonicalizing(['title' => '', 'body' => '', 'image' => ''], $notification->jsonSerialize());
    }

    public function createWithValidFields(): void
    {
        $notification = Notification::create('title', 'body')
            ->withTitle($title = 'My Title')
            ->withBody($body = 'My Body')
            ->withImageUrl($imageUrl = 'https://example.com/image.ext')
        ;
        $this->assertSame($title, $notification->title());
        $this->assertSame($body, $notification->body());
        $this->assertSame($imageUrl, $notification->imageUrl());
    }

    public function createFromValidArray(): void
    {
        $notification = Notification::fromArray($array = [
            'title' => $title = 'My Title',
            'body' => $body = 'My Body',
            'image' => $imageUrl = 'https://example.com/image.ext',
        ]);
        $this->assertSame($title, $notification->title());
        $this->assertSame($body, $notification->body());
        $this->assertSame($imageUrl, $notification->imageUrl());
        $this->assertEqualsCanonicalizing($array, $notification->jsonSerialize());
    }
}
