<?php

return [
    [
        'name' => 'default',

        'signing_secret' => env('WEBHOOKS_SECRET'),

        'header_name' => 'Signature',

        'model' => \Spatie\WebhookClient\Models\WebhookCall::class,
    ]
];
