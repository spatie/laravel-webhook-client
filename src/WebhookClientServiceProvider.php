<?php

namespace Spatie\WebhookClient;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\WebhookClient\Events\WebhookCallFailedEvent;
use Spatie\WebhookClient\Events\WebhookCallProcessingEvent;
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

        $this->app->bind(WebhookConfig::class, function ($app) {
            $routeName = Route::currentRouteName();

            $configName = Str::after($routeName, 'webhook-client-');

            $config = collect(config('webhook-client.configs'))
                ->first(function (array $config) use ($configName) {
                    return $config['name'] === $configName;
                });

            if (is_null($config)) {
                throw InvalidConfig::couldNotFindConfig($configName);
            }

            /** @var Storage\Factory $storageManger */
            $storageManger = $app['webhook-client.storage'];

            return new WebhookConfig($app, $storageManger->storage($config['webhook_storage']), $config);
        });

        Event::listen(WebhookCallProcessingEvent::class, Listeners\ResetEloquentExceptionListener::class);
        Event::listen(WebhookCallFailedEvent::class, Listeners\LogEloquentExceptionListener::class);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/webhook-client.php', 'webhook-client');

        $this->app->bind('webhook-client.storage', function ($app) {
            return new StorageManager($app);
        });
    }
}
