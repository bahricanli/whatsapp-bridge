<?php

namespace NotificationChannels\WhatsAppBridge\Exceptions;

use RuntimeException;

/**
 * Class CouldNotSendNotification
 */
final class CouldNotSendNotification extends RuntimeException
{
    public static function missingRecipient(): self
    {
        return new self(
            'WhatsApp message could not be sent: no recipient phone number provided. ' .
            'Make sure the notifiable implements routeNotificationForWhatsAppBridge() ' .
            'or use WhatsAppMessage::create()->to(\'905XXXXXXXXX\').'
        );
    }

    public static function missingContent(): self
    {
        return new self(
            'WhatsApp message could not be sent: message content is empty.'
        );
    }

    public static function bridgeConnectionFailed(string $reason): self
    {
        return new self(
            "WhatsApp bridge connection failed: {$reason}"
        );
    }

    public static function bridgeReturnedError(string $error): self
    {
        return new self(
            "WhatsApp bridge returned an error: {$error}"
        );
    }

    public static function bridgeNotConnected(): self
    {
        return new self(
            'WhatsApp bridge is not connected to WhatsApp. ' .
            'Please scan the QR code on the bridge server.'
        );
    }
}
