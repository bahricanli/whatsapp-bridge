<?php

namespace NotificationChannels\WhatsAppBridge\Tests;

use NotificationChannels\WhatsAppBridge\WhatsAppServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [WhatsAppServiceProvider::class];
    }
}
