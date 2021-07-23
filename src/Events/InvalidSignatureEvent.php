<?php

namespace Spatie\WebhookClient\Events;

use Illuminate\Http\Request;

class InvalidSignatureEvent
{
    public function __construct(
        public Request $request
    ) {
    }
}
