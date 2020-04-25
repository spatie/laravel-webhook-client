<?php

namespace Spatie\WebhookClient\WebhookResponse;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;

interface WebhookResponse
{
    public function respondToValidWebhookRequest(Request $request, WebhookConfig $config);
}
