<?php

namespace Spatie\WebhookClient\Tests\TestClasses;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookStore\WebhookStore;

class EmptyPayloadWebhookStore implements WebhookStore
{
    public function store(WebhookConfig $config, Request $request)
    {
        return $config->webhookModel::create([
            'name' => $config->name,
            'payload' => [],
        ]);
    }
}
