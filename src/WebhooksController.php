<?php

namespace Spatie\WebhookClient;

use Illuminate\Http\Request;

class WebhooksController
{
    public function __invoke(Request $request)
    {
        $eventPayload = $request->input();
    }
}

