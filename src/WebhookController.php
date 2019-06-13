<?php

namespace Spatie\WebhookClient;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
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

        $activeConfigName = Str::after($routeName, 'webhook-client-');

        $activeConfig = collect(config('webhook-client'))
            ->first(function (array $config) use ($activeConfigName) {
                return $config['name'] === $activeConfigName;
            });

        if (is_null($activeConfig)) {
            throw InvalidConfig::couldNotFindConfig($activeConfigName);
        }

        return new WebhookConfig($activeConfig);
    }
}

