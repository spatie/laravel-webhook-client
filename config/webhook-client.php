<?php

return [
    [
        'name' => 'default',

        'signing_secret' => env('WEBHOOKS_SECRET'),

        'signature_validator' => \Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,

        'model' => \Spatie\WebhookClient\Models\WebhookCall::class,
    ]
];
