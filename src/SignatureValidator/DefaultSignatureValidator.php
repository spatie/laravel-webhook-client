<?php

namespace Spatie\WebhookClient\SignatureValidator;

use Illuminate\Http\Request;
use Spatie\WebhookClient\Events\InvalidSignatureEvent;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\Exceptions\WebhookFailed;

class DefaultSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $signature = $request->header($config->signatureHeaderName);

        $this->checkSignatureExists($signature, $request, $config);

        $signingSecret = $config->signingSecret;

        if (empty($signingSecret)) {
            throw WebhookFailed::signingSecretNotSet();
        }

        $computedSignature = hash_hmac('sha256', $request->getContent(), $signingSecret);

        return hash_equals($signature, $computedSignature);
    }

    protected function checkSignatureExists($signature, Request $request, WebhookConfig $config)
    {
        if (! $signature) {
            event(new InvalidSignatureEvent($request, $signature));
            throw WebhookFailed::missingSignature($config->signatureHeaderName);
        }
    }
}
