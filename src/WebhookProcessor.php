<?php

namespace Spatie\WebhookClient;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\StripeWebhooks\Exceptions\WebhookFailed;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\Models\WebhookCall;

class WebhookProcessor
{
    /** @var \Illuminate\Http\Request */
    protected $request;

    /** @var \Spatie\WebhookClient\WebhookConfig */
    protected $config;

    public function __construct(Request $request, WebhookConfig $config)
    {
        $this->request = $request;

        $this->config = $config;
    }

    public function process()
    {
        $this->guardAgainstInvalidSignature();

        if (!$this->config->webhookProfile->shouldProcess($this->request)) {
            return;
        }

        $webhookCall = $this->storeWebhook();

        $this->processWebhook($webhookCall);
    }

    protected function guardAgainstInvalidSignature()
    {
        $headerName = $this->config->signatureHeaderName;

        $signature = $this->request->header($headerName);

        if (!$signature) {
            throw WebhookFailed::missingSignature($headerName);
        }

        if (!$this->config->signatureValidator->isValid($this->request, $this->config)) {
            throw WebhookFailed::invalidSignature($signature);
        }

        return $this;
    }

    protected function storeWebhook(): WebhookCall
    {
        return $this->config->webhookModel::create([
            'payload' => $this->request->input(),
        ]);
    }

    protected function processWebhook(WebhookCall $webhookCall): void
    {
        try {
            $job = new $this->config->processWebhookJob($webhookCall);

            dispatch($job);
        } catch (Exception $exception) {
            $webhookCall->saveException($exception);

            throw $exception;
        }
    }
}

