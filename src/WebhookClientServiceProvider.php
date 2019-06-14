<?php

namespace Spatie\WebhookClient;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\WebhookClient\Exceptions\InvalidConfig;

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
                __DIR__.'/../database/migrations/create_webhook_calls_table.php.stub' => database_path("migrations/{$timestamp}_create_webhook_calls_table.php"),
            ], 'migrations');
        }

        Route::macro('webhooks', function (string $url, string $name = 'default') {
            return Route::post($url, '\Spatie\WebhookClient\WebhookController')->name("webhook-client-{$name}");
        });

        $this->app->bind(WebhookConfig::class, function () {
            $routeName = Route::currentRouteName();

            $configName = Str::after($routeName, 'webhook-client-');

            $config = collect(config('webhook-client.configs'))
                ->first(function (array $config) use ($configName) {
                    return $config['name'] === $configName;
                });

            if (is_null($config)) {
                throw InvalidConfig::couldNotFindConfig($configName);
            }

            return new WebhookConfig($config);
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/webhook-client.php', 'webhook-client');
    }
}
