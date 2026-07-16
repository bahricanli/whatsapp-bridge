<?php

namespace NotificationChannels\WhatsAppBridge;

use NotificationChannels\WhatsAppBridge\Events\MessageWasSent;
use NotificationChannels\WhatsAppBridge\Events\SendingMessage;
use NotificationChannels\WhatsAppBridge\Exceptions\CouldNotSendNotification;

/**
 * Class WhatsApp
 *
 * Main service class — mirrors the Netgsm facade interface so it can be used
 * as a drop-in parallel channel alongside NetGSM.
 *
 * @method static array sendMessage(string|WhatsAppMessage $to, string $text = '')
 * @method static array status()
 *
 * @see WhatsAppFacade
 */
final class WhatsApp
{
    private WhatsAppClient $client;

    public function __construct(WhatsAppClient $client)
    {
        $this->client = $client;
    }

    /**
     * Send a WhatsApp message.
     *
     * Usage 1 — fluent message object:
     *   WhatsApp::sendMessage(WhatsAppMessage::create('Kodunuz: 1234')->to('905551234567'));
     *
     * Usage 2 — shorthand:
     *   WhatsApp::sendMessage('905551234567', 'Kodunuz: 1234');
     *
     * @param string|WhatsAppMessage $to   Phone number or a WhatsAppMessage instance
     * @param string                 $text Message text (used only when $to is a phone string)
     *
     * @return array Bridge response
     *
     * @throws CouldNotSendNotification
     */
    public function sendMessage($to, string $text = ''): array
    {
        if ($to instanceof WhatsAppMessage) {
            $message = $to;
        } else {
            $message = WhatsAppMessage::create($text)->to($to);
        }

        if (empty($message->to)) {
            throw CouldNotSendNotification::missingRecipient();
        }

        if (empty($message->content)) {
            throw CouldNotSendNotification::missingContent();
        }

        event(new SendingMessage($message));

        $response = $this->client->send($message->to, $message->content);

        event(new MessageWasSent($message, $response));

        return $response;
    }

    /**
     * Check bridge connection status.
     */
    public function status(): array
    {
        return $this->client->status();
    }
}
