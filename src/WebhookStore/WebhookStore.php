<?php

namespace Spatie\WebhookClient\WebhookStore;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;

interface WebhookStore
{
    public function store(WebhookConfig $config, Request $request);
}
