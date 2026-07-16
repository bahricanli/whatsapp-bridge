<?php

namespace NotificationChannels\WhatsAppBridge;

/**
 * Class WhatsAppMessage
 *
 * Represents a WhatsApp message to be sent via the Baileys bridge.
 */
final class WhatsAppMessage
{
    /**
     * The recipient phone number (international format without + or 00).
     * Example: 905551234567
     */
    public string $to = '';

    /**
     * The message text content.
     */
    public string $content = '';

    /**
     * Create a new WhatsApp message instance.
     */
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Static factory for fluent creation.
     */
    public static function create(string $content = ''): self
    {
        return new self($content);
    }

    /**
     * Set the recipient phone number.
     *
     * Accepts formats:
     *  - 905551234567   (international, no prefix)
     *  - +905551234567  (international with +)
     *  - 0905551234567  (with leading zero, will be stripped to 90...)
     *  - 5551234567     (Turkish local, will prepend 90)
     */
    public function to(string $phone): self
    {
        $this->to = self::normalizePhone($phone);

        return $this;
    }

    /**
     * Set the message text content.
     */
    public function content(string $text): self
    {
        $this->content = $text;

        return $this;
    }

    /**
     * Convert phone number to WhatsApp JID format.
     * Example: 905551234567 → 905551234567@s.whatsapp.net
     */
    public function toJid(): string
    {
        return $this->to . '@s.whatsapp.net';
    }

    /**
     * Normalize a phone number to international format without + or 00.
     */
    public static function normalizePhone(string $phone): string
    {
        // Strip all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // Remove leading 00 (international prefix)
        // str_starts_with() is PHP 8.0+; strpos(...) === 0 is the PHP 7.4-compatible equivalent.
        if (strpos($digits, '00') === 0) {
            $digits = substr($digits, 2);
        }

        // Turkish local number: starts with 0 followed by 5xx  (e.g. 05051234567 → 905051234567)
        if (strpos($digits, '05') === 0 && strlen($digits) === 11) {
            $digits = '9' . $digits;   // prepend 9, keep the leading 0  →  90 5xxx
        }

        // Turkish local without leading zero: 10 digits starting with 5
        if (strpos($digits, '5') === 0 && strlen($digits) === 10) {
            $digits = '90' . $digits;
        }

        return $digits;
    }
}
