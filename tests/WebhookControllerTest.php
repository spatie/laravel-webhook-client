<?php

namespace Spatie\WebhookClient\Tests;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfigRepository;
use Spatie\WebhookClient\Events\InvalidSignatureEvent;
use Spatie\WebhookClient\Tests\TestClasses\ProcessWebhookJobTestClass;
use Spatie\WebhookClient\Tests\TestClasses\ProcessNothingWebhookProfile;
use Spatie\WebhookClient\Tests\TestClasses\WebhookModelWithoutPayloadSaved;
use Spatie\WebhookClient\Tests\TestClasses\NothingIsValidSignatureValidator;
use Spatie\WebhookClient\Tests\TestClasses\EverythingIsValidSignatureValidator;

class WebhookControllerTest extends TestCase
{
    /** @var array */
    private $payload;

    /** @var string */
    private $signature;

    /** @var array */
    private $headers;

    public function setUp(): void
    {
        parent::setUp();

        config()->set('webhook-client.configs.0.signing_secret', 'abc123');
        config()->set('webhook-client.configs.0.process_webhook_job', ProcessWebhookJobTestClass::class);

        Route::webhooks('incoming-webhooks');

        Queue::fake();

        Event::fake();

        $this->payload = ['a' => 1];

        $this->headers = [
            config('webhook-client.configs.0.signature_header_name') => $this->determineSignature($this->payload),
        ];
    }

    /** @test */
    public function it_can_process_a_webhook_request()
    {
        $this->withoutExceptionHandling();

        $this
            ->postJson('incoming-webhooks', $this->payload, $this->headers)
            ->assertSuccessful();

        $this->assertCount(1, WebhookCall::get());
        $webhookCall = WebhookCall::first();
        $this->assertEquals('default', $webhookCall->name);
        $this->assertEquals(['a' => 1], $webhookCall->payload);

        Queue::assertPushed(ProcessWebhookJobTestClass::class, function (ProcessWebhookJobTestClass $job) {
            $this->assertEquals(1, $job->webhookCall->id);

            return true;
        });
    }

    /** @test */
    public function a_request_with_an_invalid_payload_will_not_get_processed()
    {
        $headers = $this->headers;
        $headers['Signature'] .= 'invalid';

        $this
            ->postJson('incoming-webhooks', $this->payload, $headers)
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

        $this->assertCount(0, WebhookCall::get());
        Queue::assertNothingPushed();
        Event::assertDispatched(InvalidSignatureEvent::class);
    }

    /** @test */
    public function it_can_work_with_an_alternative_signature_validator()
    {
        config()->set('webhook-client.configs.0.signature_validator', EverythingIsValidSignatureValidator::class);
        $this->refreshWebhookConfigRepository();

        $this
            ->postJson('incoming-webhooks', $this->payload, [])
            ->assertOk();

        config()->set('webhook-client.configs.0.signature_validator', NothingIsValidSignatureValidator::class);
        $this->refreshWebhookConfigRepository();

        $this
            ->postJson('incoming-webhooks', $this->payload, [])
            ->assertStatus(500);
    }

    /** @test */
    public function it_can_work_with_an_alternative_profile()
    {
        config()->set('webhook-client.configs.0.webhook_profile', ProcessNothingWebhookProfile::class);
        $this->refreshWebhookConfigRepository();

        $this
            ->postJson('incoming-webhooks', $this->payload, $this->headers)
            ->assertSuccessful();

        Queue::assertNothingPushed();
        Event::assertNotDispatched(InvalidSignatureEvent::class);
        $this->assertCount(0, WebhookCall::get());
    }

    /** @test */
    public function it_can_work_with_an_alternative_config()
    {
        Route::webhooks('incoming-webhooks-alternative-config', 'alternative-config');

        $this
            ->postJson('incoming-webhooks-alternative-config', $this->payload, $this->headers)
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

        config()->set('webhook-client.configs.0.name', 'alternative-config');
        $this->refreshWebhookConfigRepository();

        $this
            ->postJson('incoming-webhooks-alternative-config', $this->payload, $this->headers)
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_work_with_an_alternative_model()
    {
        config()->set('webhook-client.configs.0.webhook_model', WebhookModelWithoutPayloadSaved::class);
        $this->refreshWebhookConfigRepository();

        $this
            ->postJson('incoming-webhooks', $this->payload, $this->headers)
            ->assertSuccessful();

        $this->assertCount(1, WebhookCall::get());
        $this->assertEquals([], WebhookCall::first()->payload);
    }

    private function determineSignature(array $payload): string
    {
        $secret = config('webhook-client.configs.0.signing_secret');

        return hash_hmac('sha256', json_encode($payload), $secret);
    }

    /**
     * @return array
     */
    protected function getValidPayloadAndHeaders(): array
    {
        $payload = ['a' => 1];

        $headers = [
            config('webhook-client.configs.0.signature_header_name') => $this->determineSignature($payload),
        ];

        return [$payload, $headers];
    }

    protected function refreshWebhookConfigRepository()
    {
        $webhookConfig = new WebhookConfig(config('webhook-client.configs.0'));

        app(WebhookConfigRepository::class)->addConfig($webhookConfig);
    }
}
