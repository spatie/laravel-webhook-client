<?php

namespace Spatie\WebhookClient;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\WebhookClient\Exceptions\InvalidConfig;

class WebhookClientServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-webhook-client')
            ->hasConfigFile()
            ->hasMigrations('create_webhook_calls_table');
    }

    public function packageBooted()
    {
        Route::macro('webhooks', function (string $url, string $name = 'default') {
            return Route::post($url, '\Spatie\WebhookClient\Http\Controllers\WebhookController')->name("webhook-client-{$name}");
        });

        $this->app->scoped(WebhookConfigRepository::class, function () {
            $configRepository = new WebhookConfigRepository();

            collect(config('webhook-client.configs'))
                ->map(fn (array $config) => new WebhookConfig($config))
                ->each(fn (WebhookConfig $webhookConfig) => $configRepository->addConfig($webhookConfig));

            return $configRepository;
        });

        $this->app->bind(WebhookConfig::class, function () {
            $routeName = Route::currentRouteName() ?? '';

            $configName = Str::after($routeName, 'webhook-client-');

            $webhookConfig = app(WebhookConfigRepository::class)->getConfig($configName);

            if (is_null($webhookConfig)) {
                throw InvalidConfig::couldNotFindConfig($configName);
            }

            return $webhookConfig;
        });
    }
}
