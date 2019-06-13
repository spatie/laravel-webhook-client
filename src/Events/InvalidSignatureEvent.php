<?php

namespace Spatie\WebhookClient\Events;

use Illuminate\Http\Request;

class InvalidSignatureEvent
{
    /** @var \Illuminate\Http\Request */
    public $request;

    /** @var string|null */
    public $invalidSignature;

    public function __construct(Request $request, ?string $invalidSignature)
    {
        $this->request = $request;

        $this->invalidSignature = $invalidSignature;
    }
}
