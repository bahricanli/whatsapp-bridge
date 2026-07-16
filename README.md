# WhatsApp Bridge — Laravel Notification Channel

[![Latest Version](https://img.shields.io/packagist/v/bahricanli/whatsapp-bridge.svg)](https://packagist.org/packages/bahricanli/whatsapp-bridge)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.md)

Laravel notification channel for sending WhatsApp messages via a self-hosted
[Baileys](https://github.com/WhiskeySockets/Baileys) bridge.

Designed as a parallel channel alongside [bahricanli/netgsm](https://github.com/bahricanli/netgsm)
— the API is intentionally similar for easy integration.

---

## Requirements

- PHP 8.0+
- Laravel 8+
- A running Baileys WhatsApp bridge (see [docker-sip-ai-service/whatsapp-bridge](https://github.com/bahricanli/docker-sip-ai-service))

> **Older projects (PHP 7.4 / Laravel 5.5–7):** use the [`support/php74-laravel5.5`](https://github.com/bahricanli/whatsapp-bridge/tree/support/php74-laravel5.5)
> branch instead of the Packagist release. It's the same code with PHP 8-only syntax
> (union types, `mixed`, `str_starts_with()`) removed and `composer.json` constraints
> widened — no behavior change, same class/method signatures. Require it with:
> ```json
> "repositories": [
>     {"type": "vcs", "url": "https://github.com/bahricanli/whatsapp-bridge"}
> ],
> "require": {
>     "bahricanli/whatsapp-bridge": "dev-support/php74-laravel5.5"
> }
> ```

---

## Installation

```bash
composer require bahricanli/whatsapp-bridge
```

Laravel's auto-discovery registers the service provider automatically.

Publish the config file:

```bash
php artisan vendor:publish --tag=whatsapp-bridge-config
```

---

## Configuration

Add the following to your `.env`:

```env
WHATSAPP_BRIDGE_URL=http://localhost:3000
WHATSAPP_BRIDGE_API_KEY=           # optional
WHATSAPP_BRIDGE_TIMEOUT=10
```

Or edit `config/whatsapp-bridge.php` directly.

---

## Usage

### 1. Direct usage (no notification system)

```php
use NotificationChannels\WhatsAppBridge\WhatsAppFacade as WhatsApp;
use NotificationChannels\WhatsAppBridge\WhatsAppMessage;

// Shorthand
WhatsApp::sendMessage('905551234567', 'Doğrulama kodunuz: 4821');

// Fluent message
WhatsApp::sendMessage(
    WhatsAppMessage::create('Doğrulama kodunuz: 4821')->to('905551234567')
);
```

### 2. As a Laravel notification channel

**In your notification class:**

```php
use Illuminate\Notifications\Notification;
use NotificationChannels\WhatsAppBridge\WhatsAppChannel;
use NotificationChannels\WhatsAppBridge\WhatsAppMessage;

class PhoneVerificationNotification extends Notification
{
    public function __construct(private string $code) {}

    public function via($notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable): WhatsAppMessage
    {
        return WhatsAppMessage::create("Doğrulama kodunuz: {$this->code}");
        // Recipient is pulled from routeNotificationForWhatsAppBridge()
    }
}
```

**In your notifiable model (e.g. `User`):**

```php
public function routeNotificationForWhatsAppBridge(): string
{
    return $this->phone_number; // e.g. 905551234567
}
```

### 3. Parallel with NetGSM

Send via both SMS and WhatsApp simultaneously:

```php
public function via($notifiable): array
{
    return [
        \NotificationChannels\Netgsm\NetgsmChannel::class,
        \NotificationChannels\WhatsAppBridge\WhatsAppChannel::class,
    ];
}

public function toNetgsm($notifiable): string
{
    return "Doğrulama kodunuz: {$this->code}";
}

public function toWhatsApp($notifiable): WhatsAppMessage
{
    return WhatsAppMessage::create("Doğrulama kodunuz: {$this->code}");
}
```

---

## Phone Number Formats

The library normalizes phone numbers automatically:

| Input            | Stored as    |
|------------------|--------------|
| `905551234567`   | `905551234567` |
| `+905551234567`  | `905551234567` |
| `0905551234567`  | `905551234567` |
| `05551234567`    | `905551234567` |
| `5551234567`     | `905551234567` |

---

## Events

| Event | Fired |
|-------|-------|
| `SendingMessage` | Before a message is sent |
| `MessageWasSent` | After successful delivery |

```php
use NotificationChannels\WhatsAppBridge\Events\MessageWasSent;

Event::listen(MessageWasSent::class, function (MessageWasSent $event) {
    Log::info('WhatsApp sent', [
        'to'      => $event->message->to,
        'content' => $event->message->content,
    ]);
});
```

---

## Bridge Status Check

```php
use NotificationChannels\WhatsAppBridge\WhatsAppFacade as WhatsApp;

$status = WhatsApp::status();
// ['ok' => true, 'connected' => true, 'sessions' => 3]
```

---

## License

MIT — see [LICENSE.md](LICENSE.md).
