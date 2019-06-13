**WORK IN PROGRESS, DO NOT USE (YET)**

# Receive webhooks in Laravel apps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-webhook-client.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-webhook-client)
[![Build Status](https://img.shields.io/travis/spatie/laravel-webhook-client/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-webhook-client)
[![StyleCI](https://github.styleci.io/repos/191398424/shield?branch=master)](https://github.styleci.io/repos/191398424)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-webhook-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-webhook-client)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-webhook-client.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-webhook-client)

Coming soon...

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-webhook-client
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider"
```

This is the contents of the file that will be published at `config/webhook-client.php`:

```php
return [
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
         * The class name of the job that will process the webhook request.
         *
         * This should be set to a class that extends \Spatie\WebhookClient\Spatie\WebhookClient.
         */
        'process_webhook_job' => '',
    ],
];
```

This package will try to store and respond to the webhook as fast as possible. Processing the payload of the request is done via a queued.  It's recommended to not use the `sync` driver but a real queue driver.

## Usage

Coming soon

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
