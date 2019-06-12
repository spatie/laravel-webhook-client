<?php

namespace Spatie\WebhookClient;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\StripeWebhooks\Exceptions\WebhookFailed;
use Spatie\WebhookClient\Exceptions\InvalidConfig;

class WebhookController
{
    public function __invoke(Request $request)
    {
        $routeName = Route::currentRouteName();

        $config = $this->getConfig($routeName);

        $signature = $request->header($config['signature_header_name']);

        if (! $signature) {
            throw WebhookFailed::missingSignature($config['signature_header_name']);
        }

        /** @var \Spatie\WebhookClient\SignatureValidator\SignatureValidator $signatureValidator */
        $signatureValidator = app($config['signature_validator']);




    }

    protected function getConfig(string $routeName)
    {
        $activeConfigName = Str::after($routeName, 'webhook-client-');

        $activeConfig = collect(config('webhook-client'))
            ->first(function(array $config) use ($activeConfigName) {
                return $config['name'] === $activeConfigName;
            });

        if (is_null($activeConfig)) {
            throw InvalidConfig::couldNotFindConfig($activeConfigName);
        }

        return $activeConfig;

    }
}

