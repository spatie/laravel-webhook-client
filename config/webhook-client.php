<?php

return [
    [
        /*
         * This package support multiple webhook receiving endpoints. If you only have
         * one endpoint receiving webhooks, you can specify 'default'.
         */
        'name' => 'default',

        /*
         * We expect that every webhook call will  be signed using a secret. This secret
         * is used to verify that the payload has not been tampered with.
         */
        'signing_secret' => env('WEBHOOK_CLIENT_SECRET'),

        /*
         * The name of the header containing the signature.
         */
        'signature_header_name' => 'Signature',

        /*
         *  This class will verify that the signature header is valid.
         *
         * It should implement \Spatie\WebhookClient\SignatureValidator\SignatureValidator
         */
        'signature_validator' => \Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,

        /*
         * The classname of the model to be used to store call. The class should equal or extend
         * Spatie\WebhookClient\Models\WebhookCall.
         */
        'model_class' => \Spatie\WebhookClient\Models\WebhookCall::class,

        /*
         * This class is responsable to determine if the webhook call should be stored
         * and processed.
         */
        'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

        /*
         * The class name of the job that will process the webhook request.
         */
        'job_class' => '',
    ]
];
