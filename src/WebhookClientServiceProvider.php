<?php

namespace Spatie\WebhookClient;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\Exceptions\InvalidMethod;

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
        Route::macro('webhooks', function (string $url, string $name = 'default', $method = 'post') {

            if(! in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) {
                throw InvalidMethod::make($method);
            }

            return Route::{$method}($url, '\Spatie\WebhookClient\Http\Controllers\WebhookController')
                ->name("webhook-client-{$name}." . Str::random(8));
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

            $routeNameSuffix = Str::after($routeName, 'webhook-client-');

            $configName = Str::before($routeNameSuffix, '.');

            $webhookConfig = app(WebhookConfigRepository::class)->getConfig($configName);

            if (is_null($webhookConfig)) {
                throw InvalidConfig::couldNotFindConfig($configName);
            }

            return $webhookConfig;
        });
    }
}
