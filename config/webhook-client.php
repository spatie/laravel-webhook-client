<?php

return [
    'storage' => [
        /**
         * Default webhook storage driver.
         */
        'default' => env('WEBHOOK_CLIENT_STORAGE', 'eloquent'),

        /**
         * List of webhook storage drivers.
         */
        'config' => [
            'eloquent' => [
                'driver' => 'eloquent',
                'model' => env('WEBHOOK_CLIENT_ELOQUENT_MODEL', Spatie\WebhookClient\Models\EloquentWebhookCall::class),
            ],

            'memory' => [
                'driver' => 'memory',
            ],

            'cache' => [
                'driver' => 'cache',
                'store' => env('WEBHOOK_CLIENT_CACHE_STORE', 'file'),
                'lifetime' => env('WEBHOOK_CLIENT_CACHE_LIFETIME', 60),
                'prefix' => env('WEBHOOK_CLIENT_CACHE_PREFIX', 'webhook_call:'),
            ],
        ],
    ],

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
            'signature_validator' => Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,

            /*
             * This class determines if the webhook call should be stored and processed.
             */
            'webhook_profile' => Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

            /*
             * One of configured webhook storage name.
             */
            'webhook_storage' => 'eloquent',

            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \Spatie\WebhookClient\ProcessWebhookJob.
             */
            'process_webhook_job' => '',
        ],
    ],
];
