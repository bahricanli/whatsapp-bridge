<?php

namespace NotificationChannels\WhatsAppBridge\Events;

use NotificationChannels\WhatsAppBridge\WhatsAppMessage;

/**
 * Fired after a WhatsApp message was successfully sent.
 */
final class MessageWasSent
{
    public WhatsAppMessage $message;

    /** Bridge response payload, e.g. ['ok' => true] */
    public array $response;

    public function __construct(WhatsAppMessage $message, array $response)
    {
        $this->message  = $message;
        $this->response = $response;
    }
}
