<?php

namespace Spatie\WebhookClient;

use Illuminate\Support\Facades\Route;
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

        if (! class_exists('CreateWebhookCallsTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/create_webhook_calls_table.php.stub' => database_path('migrations/'.$timestamp.'_create_webhook_calls_table.php'),
            ], 'migrations');
        }

        Route::macro('webhooks', function (string $url, string $name = null) {
            return Route::post($url, '\Spatie\StripeWebhooks\StripeWebhooksController');
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/webhook-client.php', 'webhook-client');
    }
}
