<?php

namespace NotificationChannels\WhatsAppBridge;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

/**
 * Class WhatsAppServiceProvider
 */
class WhatsAppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/whatsapp-bridge.php' => config_path('whatsapp-bridge.php'),
        ], 'whatsapp-bridge-config');
    }

    /**
     * Register bindings in the container.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/whatsapp-bridge.php',
            'whatsapp-bridge'
        );

        $this->app->singleton(WhatsAppClient::class, function ($app) {
            return new WhatsAppClient(
                new Client(),
                config('whatsapp-bridge.bridge_url'),
                config('whatsapp-bridge.api_key'),
                config('whatsapp-bridge.timeout', 10),
            );
        });

        $this->app->singleton(WhatsApp::class, function ($app) {
            return new WhatsApp($app->make(WhatsAppClient::class));
        });

        $this->app->singleton('whatsapp-bridge', function ($app) {
            return $app->make(WhatsApp::class);
        });

        $this->app->singleton(WhatsAppChannel::class, function ($app) {
            return new WhatsAppChannel($app->make(WhatsApp::class));
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'whatsapp-bridge',
            WhatsApp::class,
            WhatsAppClient::class,
            WhatsAppChannel::class,
        ];
    }
}
