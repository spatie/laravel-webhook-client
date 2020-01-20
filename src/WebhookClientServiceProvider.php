<?php

namespace Spatie\WebhookClient;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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

        Route::macro('webhooks', fn (string $url, string $name = 'default') => Route::post($url, '\Spatie\WebhookClient\WebhookController')->name("webhook-client-{$name}"));

        $this->app->singleton(WebhookConfigRepository::class, function () {
            $configRepository = new WebhookConfigRepository();

            collect(config('webhook-client.configs'))
                ->map(fn (array $config) => new WebhookConfig($config))
                ->each(function (WebhookConfig $webhookConfig) use ($configRepository) {
                    $configRepository->addConfig($webhookConfig);
                });

            return $configRepository;
        });

        $this->app->bind(WebhookConfig::class, function () {
            $routeName = Route::currentRouteName();

            $configName = Str::after($routeName, 'webhook-client-');

            $webhookConfig = app(WebhookConfigRepository::class)->getConfig($configName);

            if (is_null($webhookConfig)) {
                throw InvalidConfig::couldNotFindConfig($configName);
            }

            return $webhookConfig;
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/webhook-client.php', 'webhook-client');
    }
}
