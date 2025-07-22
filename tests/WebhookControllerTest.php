<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Spatie\WebhookClient\Events\InvalidWebhookSignatureEvent;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\Tests\TestClasses\CustomRespondsToWebhook;
use Spatie\WebhookClient\Tests\TestClasses\EverythingIsValidSignatureValidator;
use Spatie\WebhookClient\Tests\TestClasses\NothingIsValidSignatureValidator;
use Spatie\WebhookClient\Tests\TestClasses\ProcessNothingWebhookProfile;
use Spatie\WebhookClient\Tests\TestClasses\ProcessWebhookJobTestClass;
use Spatie\WebhookClient\Tests\TestClasses\WebhookModelWithoutPayloadSaved;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookConfigRepository;

beforeEach(function () {
    config()->set('webhook-client.configs.0.signing_secret', 'abc123');
    config()->set('webhook-client.configs.0.process_webhook_job', ProcessWebhookJobTestClass::class);

    Route::webhooks('incoming-webhooks');

    Queue::fake();

    Event::fake();

    $this->payload = ['a' => 1];

    $this->headers = [
        config('webhook-client.configs.0.signature_header_name') => determineSignature($this->payload),
    ];
});

it('can process a webhook request', function () {
    test()->withoutExceptionHandling();

    test()
        ->postJson('incoming-webhooks', $this->payload, $this->headers)
        ->assertSuccessful();

    expect(WebhookCall::get())->toHaveCount(1);
    $webhookCall = WebhookCall::first();
    expect($webhookCall->name)->toBe('default');
    expect($webhookCall->payload)->toBe(['a' => 1]);

    Queue::assertPushed(ProcessWebhookJobTestClass::class, function (ProcessWebhookJobTestClass $job) {
        expect($job->webhookCall->id)->toBe(1);

        return true;
    });
});

it('will not process a request with an invalid payload', function () {
    $headers = $this->headers;
    $headers['Signature'] .= 'invalid';

    test()
        ->postJson('incoming-webhooks', $this->payload, $headers)
        ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

    expect(WebhookCall::get())->toHaveCount(0);
    Queue::assertNothingPushed();
    Event::assertDispatched(InvalidWebhookSignatureEvent::class);
});

it('can work with an alternative signature validator', function () {
    config()->set('webhook-client.configs.0.signature_validator', EverythingIsValidSignatureValidator::class);
    refreshWebhookConfigRepository();

    test()
        ->postJson('incoming-webhooks', $this->payload, [])
        ->assertStatus(200);

    config()->set('webhook-client.configs.0.signature_validator', NothingIsValidSignatureValidator::class);
    refreshWebhookConfigRepository();

    test()
        ->postJson('incoming-webhooks', $this->payload, [])
        ->assertStatus(500);
});

it('can work with an alternative profile', function () {
    config()->set('webhook-client.configs.0.webhook_profile', ProcessNothingWebhookProfile::class);
    refreshWebhookConfigRepository();

    test()
        ->postJson('incoming-webhooks', $this->payload, $this->headers)
        ->assertSuccessful();

    Queue::assertNothingPushed();
    Event::assertNotDispatched(InvalidWebhookSignatureEvent::class);
    expect(WebhookCall::get())->toHaveCount(0);
});

it('can work with an alternative config', function () {
    Route::webhooks('incoming-webhooks-alternative-config', 'alternative-config');

    test()
        ->postJson('incoming-webhooks-alternative-config', $this->payload, $this->headers)
        ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

    config()->set('webhook-client.configs.0.name', 'alternative-config');
    refreshWebhookConfigRepository();

    test()
        ->postJson('incoming-webhooks-alternative-config', $this->payload, $this->headers)
        ->assertSuccessful();
});

it('can work with an alternative model', function () {
    test()->withoutExceptionHandling();

    config()->set('webhook-client.configs.0.webhook_model', WebhookModelWithoutPayloadSaved::class);
    refreshWebhookConfigRepository();

    test()
        ->postJson('incoming-webhooks', $this->payload, $this->headers)
        ->assertSuccessful();

    expect(WebhookCall::get())->toHaveCount(1);
    expect(WebhookCall::first()->payload)->toBe([]);
});

it('can respond with custom response', function () {
    config()->set('webhook-client.configs.0.webhook_response', CustomRespondsToWebhook::class);

    test()
        ->postJson('incoming-webhooks', $this->payload, $this->headers)
        ->assertSuccessful()
        ->assertJson([
            'foo' => 'bar',
        ]);
});

it('can store a specific header', function () {
    test()->withoutExceptionHandling();

    config()->set('webhook-client.configs.0.store_headers', ['Signature']);

    test()
        ->postJson('incoming-webhooks', $this->payload, $this->headers)
        ->assertSuccessful();

    expect(WebhookCall::get())->toHaveCount(1);
    expect(WebhookCall::first()->headers)->toHaveCount(1);
    expect(WebhookCall::first()->headerBag()->get('Signature'))->toBe($this->headers['Signature']);
});

it('can store all headers', function () {
    test()->withoutExceptionHandling();

    config()->set('webhook-client.configs.0.store_headers', '*');

    test()
        ->postJson('incoming-webhooks', $this->payload, $this->headers)
        ->assertSuccessful();

    expect(WebhookCall::get())->toHaveCount(1);
    expect(count(WebhookCall::first()->headers))->toBeGreaterThan(1);
});

it('can store none of the headers', function () {
    test()->withoutExceptionHandling();

    config()->set('webhook-client.configs.0.store_headers', []);

    test()
        ->postJson('incoming-webhooks', $this->payload, $this->headers)
        ->assertSuccessful();

    expect(WebhookCall::get())->toHaveCount(1);
    expect(count(WebhookCall::first()->headers))->toBe(0);
});

it('allows multiple routes to share configuration', function () {
    config()->set('webhook-client.add_unique_token_to_route_name', true);

    Route::webhooks('incoming-webhooks-additional');

    refreshWebhookConfigRepository();

    $routeNames = collect(Route::getRoutes())
        ->map(fn ($route) => $route->getName());

    $uniqueRouteNames = $routeNames->unique();

    expect($routeNames->count())->toBe($uniqueRouteNames->count());
});

function determineSignature(array $payload): string
{
    $secret = config('webhook-client.configs.0.signing_secret');

    return hash_hmac('sha256', json_encode($payload), $secret);
}

function getValidPayloadAndHeaders(): array
{
    $payload = ['a' => 1];

    $headers = [
        config('webhook-client.configs.0.signature_header_name') => determineSignature($payload),
    ];

    return [$payload, $headers];
}

function refreshWebhookConfigRepository(): void
{
    $webhookConfig = new WebhookConfig(config('webhook-client.configs.0'));

    app(WebhookConfigRepository::class)->addConfig($webhookConfig);
}
