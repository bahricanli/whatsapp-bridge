<?php

namespace NotificationChannels\WhatsAppBridge;

use Illuminate\Support\Facades\Facade;

/**
 * Class WhatsAppFacade
 *
 * @method static array sendMessage(string|WhatsAppMessage $to, string $text = '')
 * @method static array status()
 *
 * @see WhatsApp
 */
final class WhatsAppFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'whatsapp-bridge';
    }
}
