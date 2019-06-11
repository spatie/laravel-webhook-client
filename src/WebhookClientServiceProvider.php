<?php

namespace Spatie\WebhookClient;

use Illuminate\Support\ServiceProvider;

class WebhookClientServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/webhook-client.php' => config_path('webhook-client.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/webhook-client.php', 'webhook-client');
    }
}
