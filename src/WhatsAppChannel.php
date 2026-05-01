<?php

namespace NotificationChannels\WhatsAppBridge;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
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
 *
 * Failures (bridge unreachable, timeout, etc.) are logged as warnings and
 * swallowed so they never cause an HTTP 500 in the calling application.
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
     * Bridge errors are caught and logged — they never propagate to the caller.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        try {
            $message = $notification->toWhatsApp($notifiable);

            if ($message instanceof WhatsAppMessage) {
                // If no recipient set on message, pull it from the notifiable
                if (empty($message->to)) {
                    $to = $notifiable->routeNotificationFor('WhatsAppBridge', $notification);

                    if (empty($to)) {
                        Log::warning('WhatsApp notification skipped: missing recipient');
                        return;
                    }

                    $message->to($to);
                }

                $this->whatsApp->sendMessage($message);

                return;
            }

            // Plain string content — recipient must come from the notifiable
            $to = $notifiable->routeNotificationFor('WhatsAppBridge', $notification);

            if (empty($to)) {
                Log::warning('WhatsApp notification skipped: missing recipient');
                return;
            }

            $this->whatsApp->sendMessage($to, (string) $message);

        } catch (CouldNotSendNotification $e) {
            // Bridge is down, timed out, or returned an error.
            // Log the failure but do NOT rethrow — WhatsApp is a secondary channel
            // and must never block or crash the primary flow (e.g. SMS + HTTP response).
            Log::warning('WhatsApp notification failed (non-fatal): ' . $e->getMessage());
        }
    }
}
