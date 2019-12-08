<?php

namespace Spatie\WebhookClient\Events;

use Illuminate\Http\Request;

class InvalidSignatureEvent
{
    /** @var \Illuminate\Http\Request */
    public Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
