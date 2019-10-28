<?php

namespace Spatie\WebhookClient\Storage;

use Illuminate\Http\Request;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

interface WebhookCallStorage
{
    /**
     * Store given webhook call.
     *
     * @param WebhookConfig $config
     * @param Request $request
     * @return bool
     */
    public function storeWebhookCall(WebhookConfig $config, Request $request): WebhookCall;

    /**
     * Retrieve a webhook by given id.
     *
     * @param string $id
     * @return WebhookCall
     */
    public function retrieveWebhookCall(string $id): WebhookCall;

    /**
     * Delete given webhook from storage.
     *
     * @param string $id
     * @return bool
     */
    public function deleteWebhookCall(string $id): bool;
}
