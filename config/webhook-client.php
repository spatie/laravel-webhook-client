<?php

return [
    [
        'name' => 'default',

        'signing_secret' => env('WEBHOOK_CLIENT_SECRET'),

        'signature_header_name' => 'Signature',

        'signature_validator' => \Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,

        'model_class' => \Spatie\WebhookClient\Models\WebhookCall::class,
    ]
];
