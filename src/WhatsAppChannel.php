<?php

namespace NotificationChannels\WhatsAppBridge;

use Illuminate\Notifications\Notification;
use NotificationChannels\WhatsAppBridge\Exceptions\CouldNotSendNotification;

/**
 * Class WhatsAppChannel
 *
 * Laravel notification channel. Use this class in the via() method of your
 * notification to route it through WhatsApp.
 *
 * Your notification class must implement toWhatsApp($notifiable) returning
 * either a WhatsAppMessage or a plain string.
 *
 * Your notifiable model must implement routeNotificationForWhatsAppBridge()
 * returning the phone number when a plain string message is used.
 */
final class WhatsAppChannel
{
    private WhatsApp $whatsApp;

    public function __construct(WhatsApp $whatsApp)
    {
        $this->whatsApp = $whatsApp;
    }

    /**
     * Send the given notification.
     *
     * @throws CouldNotSendNotification
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        $message = $notification->toWhatsApp($notifiable);

        if ($message instanceof WhatsAppMessage) {
            // If no recipient set on message, pull it from the notifiable
            if (empty($message->to)) {
                $to = $notifiable->routeNotificationFor('WhatsAppBridge', $notification);

                if (empty($to)) {
                    throw CouldNotSendNotification::missingRecipient();
                }

                $message->to($to);
            }

            $this->whatsApp->sendMessage($message);

            return;
        }

        // Plain string content — recipient must come from the notifiable
        $to = $notifiable->routeNotificationFor('WhatsAppBridge', $notification);

        if (empty($to)) {
            throw CouldNotSendNotification::missingRecipient();
        }

        $this->whatsApp->sendMessage($to, (string) $message);
    }
}
