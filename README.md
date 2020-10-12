# Receive webhooks in Laravel apps

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-webhook-client.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-webhook-client)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/spatie/laravel-webhook-client/run-tests?label=tests)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-webhook-client.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-webhook-client)

A webhook is a way for an app to provide information to another app about a specific event. The way the two apps communicate is with a simple HTTP request.

This package allows you to receive webhooks in a Laravel app. It has support for [verifying signed calls](#verifying-the-signature-of-incoming-webhooks), [storing payloads and processing the payloads](#storing-and-processing-webhooks) in a queued job.

If you need to send webhooks, take a look at our [laravel-webhook-server](https://github.com/spatie/laravel-webhook-server) package.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-webhook-client.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-webhook-client)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-webhook-client
```

### Configuring the package

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="config"
```

This is the contents of the file that will be published at `config/webhook-client.php`:

```php
return [
    'configs' => [
        [
            /*
             * This package supports multiple webhook receiving endpoints. If you only have
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
             * This class determines the response on a valid webhook call.
             */
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,

            /*
             * The classname of the model to be used to store call. The class should be equal
             * or extend Spatie\WebhookClient\Models\WebhookCall.
             */
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,

            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \Spatie\WebhookClient\ProcessWebhookJob.
             */
            'process_webhook_job' => '',
        ],
    ],
```

In the `signing_secret` key of the config file, you should add a valid webhook secret. This value should be provided by the app that will send you webhooks.

This package will try to store and respond to the webhook as fast as possible. Processing the payload of the request is done via a queued job.  It's recommended to not use the `sync` driver but a real queue driver. You should specify the job that will handle processing webhook requests in the `process_webhook_job` of the config file. A valid job is any class that extends `Spatie\WebhookClient\ProcessWebhookJob` and has a `handle` method.

### Preparing the database

By default, all webhook calls will get saved in the database.

To create the table that holds the webhook calls, you must publish the migration with:
```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="migrations"
```

After the migration has been published, you can create the `webhook_calls` table by running the migrations:

```bash
php artisan migrate
```

### Taking care of routing

Finally, let's take care of the routing. At the app that sends webhooks, you probably configure an URL where you want your webhook requests to be sent. In the routes file of your app, you must pass that route to `Route::webhooks`. Here's an example:

```php
Route::webhooks('webhook-receiving-url');
```

Behind the scenes, this will register a `POST` route to a controller provided by this package. Because the app that sends webhooks to you has no way of getting a csrf-token, you must add that route to the `except` array of the `VerifyCsrfToken` middleware:

```php
protected $except = [
    'webhook-receiving-url',
];
```

## Usage

With the installation out of the way, let's take a look at how this package handles webhooks. First, it will verify if the signature of the request is valid. If it is not, we'll throw an exception and fire off the `InvalidSignatureEvent` event. Requests with invalid signatures will not be stored in the database.

Next, the request will be passed to a webhook profile. A webhook profile is a class that determines if a request should be stored and processed by your app. It allows you to filter out webhook requests that are of interest to your app. You can easily create [your own webhook profile](#determining-which-webhook-requests-should-be-stored-and-processed).

If the webhook profile determines that request should be stored and processed, we'll first store it in the `webhook_calls` table. After that, we'll pass that newly created `WebhookCall` model to a queued job. Most webhook sending apps expect you to respond very quickly. Offloading the real processing work allows for speedy responses. You can specify which job should process the webhook in the `process_webhook_job` in the `webhook-client` config file. Should an exception be thrown while queueing the job, the package will store that exception in the `exception` attribute on the `WebhookCall` model.

After the job has been dispatched, the request will be passed to a webhook response. A webhook response is a class that determines the HTTP response for the request. An 'ok' message response with `200` status code is returned by default, but you can easily create [your own webhook response](#creating-your-own-webhook-response).

### Verifying the signature of incoming webhooks

This package assumes that an incoming webhook request has a header that can be used to verify the payload has not been tampered with. The name of the header containing the signature can be configured in the `signature_header_name` key of the config file. By default, the package uses the `DefaultSignatureValidator` to validate signatures. This is how that class will compute the signature.

```php
$computedSignature = hash_hmac('sha256', $request->getContent(), $configuredSigningSecret);
```

If the `$computedSignature` does match the value, the request will be [passed to the webhook profile](#determining-which-webhook-requests-should-be-stored-and-processed). If  `$computedSignature` does not match the value in the signature header, the package will respond with a `500` and discard the request.

### Creating your own signature validator

A signature validator is any class that implements `Spatie\WebhookClient\SignatureValidator\SignatureValidator`. Here's what that interface looks like.

```php
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;

interface SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool;
}
```

`WebhookConfig` is a data transfer object that lets you easily pull up the config (containing the header name that contains the signature and the secret) for the webhook request.

After creating your own `SignatureValidator` you must register it in the `signature_validator` in the `webhook-client` config file.

### Determining which webhook requests should be stored and processed

After the signature of an incoming webhook request is validated, the request will be passed to a webhook profile. A webhook profile is a class that determines if the request should be stored and processed. If the webhook sending app sends out request where your app isn't interested in, you can use this class to filter out such events.

By default the `\Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile` class is used. As its name implies, this default class will determine that all incoming requests should be stored and processed.

### Creating your own webhook profile

A webhook profile is any class that implements `\Spatie\WebhookClient\WebhookProfile\WebhookProfile`. This is what that interface looks like:

```php
namespace Spatie\WebhookClient\WebhookProfile;

use Illuminate\Http\Request;

interface WebhookProfile
{
    public function shouldProcess(Request $request): bool;
}
```

After creating your own `WebhookProfile` you must register it in the `webhook_profile` key in the `webhook-client` config file.

### Storing and processing webhooks

After the signature is validated and the webhook profile has determined that the request should be processed, the package will store and process the request.

The request will first be stored in the `webhook_calls` table. This is done using the `WebhookCall` model.

Should you want to customize the table name or anything on the storage behavior, you can let the package use an alternative model. A webhook storing model can be specified in the `webhook_model`. Make sure you model extends `Spatie\WebhookClient\Models\WebhookCall`.

You can change how the webhook is stored by overriding the `storeWebhook` method of `WebhookCall`. In the `storeWebhook` method you should return a saved model.

Next, the newly created `WebhookCall` model will be passed to a queued job that will process the request. Any class that extends `\Spatie\WebhookClient\ProcessWebhookJob` is a valid job. Here's an example:

```php
namespace App\Jobs;

use \Spatie\WebhookClient\ProcessWebhookJob as SpatieProcessWebhookJob;

class ProcessWebhookJob extends SpatieProcessWebhookJob
{
    public function handle()
    {
        // $this->webhookCall // contains an instance of `WebhookCall`

        // perform the work here
    }
}
```

You should specify the class name of your job in the `process_webhook_job` of the `webhook-client` config file.

### Creating your own webhook response

A webhook response is any class that implements `\Spatie\WebhookClient\WebhookResponse\RespondsToWebhook`. This is what that interface looks like:

```php
namespace Spatie\WebhookClient\WebhookResponse;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;

interface RespondsToWebhook
{
    public function respondToValidWebhook(Request $request, WebhookConfig $config);
}
```

After creating your own `WebhookResponse` you must register it in the `webhook_response` key in the `webhook-client` config file.

### Handling incoming webhook request for multiple apps

This package allows webhooks to be received from multiple different apps. Let's take a look at an example config file where we add support for two webhook URLs. All comments from the config have been removed for brevity.

```php
return [
    'configs' => [
        [
            'name' => 'webhook-sending-app-1',
            'signing_secret' => 'secret-for-webhook-sending-app-1',
            'signature_header_name' => 'Signature-for-app-1',
            'signature_validator' => \Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'process_webhook_job' => '',
        ],
        [
            'name' => 'webhook-sending-app-2',
            'signing_secret' => 'secret-for-webhook-sending-app-2',
            'signature_header_name' => 'Signature-for-app-2',
            'signature_validator' => \Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'process_webhook_job' => '',
        ],
    ],
];
```

When registering routes for the package, you should pass the `name` of the config as a second parameter.

```php
Route::webhooks('receiving-url-for-app-1', 'webhook-sending-app-1');
Route::webhooks('receiving-url-for-app-2', 'webhook-sending-app-2');
```

### Using the package without a controller

If you don't want to use the routes and controller provided by your macro, you can programmatically add support for webhooks to your own controller.

`Spatie\WebhookClient\WebhookProcessor` is a class that verifies the signature, calls the web profile, stores the webhook request, and starts a queued job to process the stored webhook request. The controller provided by this package also uses that class [under the hood](https://github.com/spatie/laravel-webhook-client/blob/2172f79eda7d6f86a01554be9b444b9e31343610/src/WebhookController.php#L11).

It can be used like this:

```php
$webhookConfig = new \Spatie\WebhookClient\WebhookConfig([
    'name' => 'webhook-sending-app-1',
    'signing_secret' => 'secret-for-webhook-sending-app-1',
    'signature_header_name' => 'Signature',
    'signature_validator' => \Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,
    'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
    'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
    'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
    'process_webhook_job' => '',
]);

(new \Spatie\WebhookClient\WebhookProcessor($request, $webhookConfig))->process();
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment, we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Kruikstraat 22, 2018 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
