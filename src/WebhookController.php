<?php

namespace Spatie\WebhookClient;

use Illuminate\Http\Request;

class WebhookController
{
    public function __invoke(Request $request, WebhookConfig $config)
    {
        (new WebhookProcessor($request, $config))->process();
        if ($class = $config->webhookResponse) {
            $handler = new $class;

            return $handler->response($request);
        }

        return response()->json(['message' => 'ok']);
    }
}
