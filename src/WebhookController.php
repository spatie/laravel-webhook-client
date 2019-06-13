<?php

namespace Spatie\WebhookClient;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\StripeWebhooks\Exceptions\WebhookFailed;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\Models\WebhookCall;

class WebhookController
{
    public function __invoke(Request $request)
    {
        $config = $this->getConfig();

        $this->guardAgainstInvalidSignature($request, $config);

        if (!$config->webhookProfile->shouldProcess($request)) {
            return;
        }

        $webhookCall = $this->storeWebhook($request, $config);

        try {
            $webhookCall->process();
        } catch (Exception $exception) {
            $webhookCall->saveException($exception);

            throw $exception;
        }

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

    protected function guardAgainstInvalidSignature(Request $request, WebhookConfig $config)
    {
        $signature = $request->header($config['signature_header_name']);

        if (!$signature) {
            throw WebhookFailed::missingSignature($config['signature_header_name']);
        }

        if (!$config->signatureValidator->isValid($request, $config)) {
            throw WebhookFailed::invalidSignature($signature);
        }

        return $this;
    }

    protected function storeWebhook(Request $request, WebhookConfig $config): WebhookCall
    {
        return $config->modelClass::create([
            'payload' => $request->input(),
        ]);
    }
}

