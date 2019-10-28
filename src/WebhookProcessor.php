<?php

namespace Spatie\WebhookClient;

use Exception;
use Illuminate\Http\Request;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\Exceptions\WebhookFailed;
use Spatie\WebhookClient\Events\InvalidSignatureEvent;
use Spatie\WebhookClient\Events\WebhookCallFailedEvent;
use Spatie\WebhookClient\Events\WebhookCallProcessingEvent;

class WebhookProcessor
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var WebhookConfig
     */
    protected $config;

    /**
     * @param Request $request
     * @param WebhookConfig $config
     */
    public function __construct(Request $request, WebhookConfig $config)
    {
        $this->request = $request;

        $this->config = $config;
    }

    /**
     * Process given webhook.
     *
     * @return WebhookCall|null
     * @throws WebhookFailed
     * @throws Exception
     */
    public function process()
    {
        $this->ensureValidSignature();

        if (! $this->config->webhookProfile->shouldProcess($this->request)) {
            return;
        }

        $webhookCall = $this->storeWebhook();

        $this->processWebhook($webhookCall);

        return $webhookCall;
    }

    /**
     * @return $this
     * @throws WebhookFailed
     */
    protected function ensureValidSignature()
    {
        if (! $this->config->signatureValidator->isValid($this->request, $this->config)) {
            event(new InvalidSignatureEvent($this->request));

            throw WebhookFailed::invalidSignature();
        }

        return $this;
    }

    /**
     * Store given webhook to a storage.
     *
     * @return WebhookCall
     */
    protected function storeWebhook(): WebhookCall
    {
        return $this->config->webhookStorage->storeWebhookCall($this->config, $this->request);
    }

    /**
     * @param WebhookCall $webhookCall
     * @throws Exception
     */
    protected function processWebhook(WebhookCall $webhookCall): void
    {
        try {
            event(new WebhookCallProcessingEvent($webhookCall));

            dispatch(new $this->config->processWebhookJob($webhookCall));
        } catch (\Exception $e) {
            event(new WebhookCallFailedEvent($webhookCall, $e));

            throw $e;
        }
    }
}
