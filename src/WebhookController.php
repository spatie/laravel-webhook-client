<?php

namespace Spatie\WebhookClient;

use Illuminate\Http\Request;

class WebhookController
{
    public function __invoke(Request $request, WebhookConfig $config)
    {
        (new WebhookProcessor($request, $config))->process();

        return response()->json(['message' => 'ok']);
    }
}
