<?php

namespace Spatie\WebhookClient;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\WebhookClient\Exceptions\InvalidConfig;

class WebhookController
{
    public function __invoke(Request $request)
    {
        $config = $this->getConfig();

        (new WebhookProcessor($request, $config))->process();

        return response()->json(['message' => 'ok']);
    }

    protected function getConfig(): WebhookConfig
    {
        $routeName = Route::currentRouteName();

        $configName = Str::after($routeName, 'webhook-client-');

        $config = collect(config('webhook-client'))
            ->first(function (array $config) use ($configName) {
                return $config['name'] === $configName;
            });

        if (is_null($config)) {
            throw InvalidConfig::couldNotFindConfig($configName);
        }

        return new WebhookConfig($config);
    }
}
