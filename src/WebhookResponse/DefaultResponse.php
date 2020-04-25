<?php

namespace Spatie\WebhookClient\WebhookResponse;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;

class DefaultResponse implements WebhookResponse
{
    public function respondToValidWebhookRequest(Request $request, WebhookConfig $config)
    {
        return response()->json(['message' => 'ok']);
    }
}
