<?php

namespace NotificationChannels\WhatsAppBridge\Events;

use NotificationChannels\WhatsAppBridge\WhatsAppMessage;

/**
 * Fired just before a WhatsApp message is sent.
 */
final class SendingMessage
{
    public WhatsAppMessage $message;

    public function __construct(WhatsAppMessage $message)
    {
        $this->message = $message;
    }
}
