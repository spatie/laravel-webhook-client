**WORK IN PROGRESS, DO NOT USE (YET)**

# Receive webhooks in Laravel apps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-webhook-client.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-webhook-client)
[![Build Status](https://img.shields.io/travis/spatie/laravel-webhook-client/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-webhook-client)
[![StyleCI](https://github.styleci.io/repos/191398424/shield?branch=master)](https://github.styleci.io/repos/191398424)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-webhook-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-webhook-client)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-webhook-client.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-webhook-client)

A webhook is a way for an app to provide information to another app about a certain event. The way the two apps communicate is with a simple HTTP request. 

This package allows you to easily receive webhooks in a Laravel app. It has support for [verifying signed calls](TODO:add link), storing payloads and processing the payloads in a queued job.

If you need to send webhooks take a look at our [laravel-webhook-server](https://github.com/spatie/laravel-webhook-server) package.

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-webhook-client
```

### Configuring the package

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

In the `signing_secret` key of the config file you should add a valid webhook secret. This value should be provided by the app that will send you webhooks.

This package will try to store and respond to the webhook as fast as possible. Processing the payload of the request is done via a queued.  It's recommended to not use the `sync` driver but a real queue driver. You should specify the job that will handle processing webhook requests in the `process_webhook_job` of the config file. A valid job is any class that extends `Spatie\WebhookClient\ProcessWebhookJob`. 

### Preparing the database

By default, all webhook calls will get saved in the database.

To create the table that holds the webhook calls, you must publish the migration with:
```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="migrations"
```

After the migration has been published you can create the `webhook_calls` table by running the migrations:

```bash
php artisan migrate
```

### Taking care of routing

Finally, let's take care of the routing. At the app that sends webhooks you probably configure an url where you want your webhook requests to be sent. In the routes file of your app you must pass that route to `Route::webhooks`. Here's an example:

```php
Route::webHooks('webhook-receiving-url')
```

Behind the scenes this will register a `POST` route to a controller provided by this package. Because the app that send webhooks to you has no way of getting a csrf-token, you must add that route to the `except` array of the `VerifyCsrfToken` middleware:

```php
protected $except = [
    'webhook-receiving-url',
];
```

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
