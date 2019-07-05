<?php

namespace Spatie\WebhookClient\WebhookStore;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;

class DefaultWebhookStore implements WebhookStore
{
    public function store(WebhookConfig $config, Request $request)
    {
        return $config->webhookModel::create([
            'name' => $config->name,
            'payload' => $request->input(),
        ]);
    }
}
