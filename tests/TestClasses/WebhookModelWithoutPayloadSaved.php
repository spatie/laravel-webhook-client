<?php

namespace Spatie\WebhookClient\Tests\TestClasses;

use Illuminate\Http\Request;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

class WebhookModelWithoutPayloadSaved extends WebhookCall
{
    public static function storeWebhook(WebhookConfig $config, Request $request): WebhookCall
    {
        return WebhookCall::create([
            'name' => $config->name,
            'url' => 'https://example.com',
            'payload' => [],
        ]);
    }
}
