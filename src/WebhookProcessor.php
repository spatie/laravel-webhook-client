<?php

namespace Spatie\WebhookClient;

use Exception;
use Illuminate\Http\Request;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\Exceptions\WebhookFailed;
use Spatie\WebhookClient\Events\InvalidSignatureEvent;

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

        if (! $this->config->webhookProfile->shouldProcess($this->request)) {
            return;
        }

        $webhookCall = $this->config->webhookStore->store($this->config, $this->request);

        $this->processWebhook($webhookCall);
    }

    protected function guardAgainstInvalidSignature()
    {
        $headerName = $this->config->signatureHeaderName;

        $signature = $this->request->header($headerName);

        if (! $signature) {
            event(new InvalidSignatureEvent($this->request, $signature));
            throw WebhookFailed::missingSignature($headerName);
        }

        if (! $this->config->signatureValidator->isValid($this->request, $this->config)) {
            event(new InvalidSignatureEvent($this->request, $signature));

            throw WebhookFailed::invalidSignature($signature, $this->config->signatureHeaderName);
        }

        return $this;
    }

    protected function processWebhook(WebhookCall $webhookCall): void
    {
        try {
            $job = new $this->config->processWebhookJob($webhookCall);

            $webhookCall->clearException();

            dispatch($job);
        } catch (Exception $exception) {
            $webhookCall->saveException($exception);

            throw $exception;
        }
    }
}
