<?php

namespace Spatie\WebhookClient\Events;

use Illuminate\Http\Request;

class InvalidSignatureEvent
{
    /** @var \Illuminate\Http\Request */
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
