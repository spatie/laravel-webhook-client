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
        $this->ensureValidSignature();

        if (! $this->config->webhookProfile->shouldProcess($this->request)) {
            return;
        }

        $webhookCall = $this->storeWebhook();

        $this->processWebhook($webhookCall);
    }

    protected function ensureValidSignature()
    {
        if (! $this->config->signatureValidator->isValid($this->request, $this->config)) {
            event(new InvalidSignatureEvent($this->request));

            throw WebhookFailed::invalidSignature();
        }

        return $this;
    }

    protected function storeWebhook(): WebhookCall
    {
        return $this->config->webhookModel::storeWebhook($this->config, $this->request);
    }

    protected function processWebhook(WebhookCall $webhookCall): void
    {
        try {
            $job = new $this->config->processWebhookJob($webhookCall);

            $webhookCall->clearException();

            dispatch($job)->onConnection('sync');
        } catch (Exception $exception) {
            $webhookCall->saveException($exception);

            throw $exception;
        }
    }
}
