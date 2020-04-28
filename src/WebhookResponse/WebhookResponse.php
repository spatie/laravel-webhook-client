<?php

namespace Spatie\WebhookClient\WebhookResponse;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Symfony\Component\HttpFoundation\Response;

interface WebhookResponse
{
    public function respondToValidWebhookRequest(Request $request, WebhookConfig $config): Response;
}
