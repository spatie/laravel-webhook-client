<?php

namespace Spatie\WebhookClient;

use Illuminate\Http\Request;

class WebhookController
{
    /**
     * @param Request $request
     * @param WebhookConfig $config
     * @return \Illuminate\Http\JsonResponse
     * @throws Exceptions\WebhookFailed
     */
    public function __invoke(Request $request, WebhookConfig $config)
    {
        $webhookCall = (new WebhookProcessor($request, $config))->process();

        return response()->json([
            'reference' => $webhookCall ? $webhookCall->getId() : null,
        ]);
    }
}
