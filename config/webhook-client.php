<?php

return [
    'configs' => [
        [
            /*
             * This package support multiple webhook receiving endpoints. If you only have
             * one endpoint receiving webhooks, you can use 'default'.
             */
            'name' => 'default',

            /*
             * We expect that every webhook call will be signed using a secret. This secret
             * is used to verify that the payload has not been tampered with.
             */
            'signing_secret' => env('WEBHOOK_CLIENT_SECRET'),

            /*
             * The name of the header containing the signature.
             */
            'signature_header_name' => 'Signature',

            /*
             *  This class will verify that the content of the signature header is valid.
             *
             * It should implement \Spatie\WebhookClient\SignatureValidator\SignatureValidator
             */
            'signature_validator' => \Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,

            /*
             * This class determines if the webhook call should be stored and processed.
             */
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

            /*
             * The classname of the model to be used to store call. The class should be equal
             * or extend Spatie\WebhookClient\Models\WebhookCall.
             */
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,

            /*
             * This class will store a webhook model in the database. It should return a
             * model equal or extended from Spatie\WebhookClient\Models\WebhookCall.
             *
             * It should implement \Spatie\WebhookClient\WebhookStore\WebhookStore
             */
            'webhook_store' => \Spatie\WebhookClient\WebhookStore\DefaultWebhookStore::class,

            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \Spatie\WebhookClient\ProcessWebhookJob.
             */
            'process_webhook_job' => '',
        ],
    ],
];
