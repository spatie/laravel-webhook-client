<?php

namespace Spatie\WebhookClient\Tests\TestClasses;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookResponse\WebhookResponse;

class CustomWebhookResponse implements WebhookResponse
{
    public function respondToValidWebhookRequest(Request $request, WebhookConfig $config)
    {
        return response()->json(['foo' => 'bar']);
    }
}
