<?php

namespace NotificationChannels\WhatsAppBridge\Tests\Unit;

use NotificationChannels\WhatsAppBridge\Tests\TestCase;
use NotificationChannels\WhatsAppBridge\WhatsAppMessage;

class WhatsAppMessageTest extends TestCase
{
    public function test_it_normalizes_turkish_phone_numbers(): void
    {
        $this->assertSame('905551234567', WhatsAppMessage::normalizePhone('+90 555 123 45 67'));
        $this->assertSame('905551234567', WhatsAppMessage::normalizePhone('05551234567'));
        $this->assertSame('905551234567', WhatsAppMessage::normalizePhone('5551234567'));
    }

    public function test_it_registers_the_notification_channel(): void
    {
        $this->assertTrue($this->app->bound(\NotificationChannels\WhatsAppBridge\WhatsAppChannel::class));
    }
}
