<?php

namespace Spatie\WebhookClient\Events;

use Illuminate\Http\Request;

class InvalidWebhookSignatureEvent
{
    public function __construct(
        public Request $request
    ) {
    }
}
