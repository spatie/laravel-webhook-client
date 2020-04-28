<?php

namespace Spatie\WebhookClient\WebhookResponse;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Symfony\Component\HttpFoundation\Response;

class DefaultResponse implements WebhookResponse
{
    public function respondToValidWebhookRequest(Request $request, WebhookConfig $config): Response
    {
        return response()->json(['message' => 'ok']);
    }
}
