<?php

namespace Spatie\WebhookClient\Tests\TestClasses;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookResponse\WebhookResponse;
use Symfony\Component\HttpFoundation\Response;

class CustomWebhookResponse implements WebhookResponse
{
    public function respondToValidWebhookRequest(Request $request, WebhookConfig $config): Response
    {
        return response()->json(['foo' => 'bar']);
    }
}
