<?php

namespace Spatie\WebhookClient\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\Tests\TestClasses\ProcessWebhookJobTestClass;

class WebhookControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config()->set('webhook-client.0.signing_secret', 'abc123');
        config()->set('webhook-client.0.process_webhook_job', ProcessWebhookJobTestClass::class);

        Route::webhooks('incoming-webhooks');

        Queue::fake();
    }

    /** @test */
    public function it_can_process_a_webhook_request()
    {
        $payload = ['a' => 1];

        $headers = [
            config('webhook-client.0.signature_header_name') => $this->determineSignature($payload)
        ];

        $this
            ->postJson('incoming-webhooks', $payload, $headers)
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

    private function determineSignature(array $payload): string
    {
        $secret = config('webhook-client.0.signing_secret');

        return hash_hmac('sha256', json_encode($payload), $secret);
    }
}

