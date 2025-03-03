<?php

namespace Spatie\WebhookClient\Tests\Unit;

use Illuminate\Http\Request;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator;
use Spatie\WebhookClient\Tests\TestCase;
use Spatie\WebhookClient\Tests\TestClasses\ProcessWebhookJobTestClass;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile;

class WebhookCallTest extends TestCase
{
    /** @test */
    public function it_stores_url_without_truncation_when_under_255_characters()
    {
        $url = 'https://example.com/webhook?param=value';
        $request = Request::create($url);
        $config = new WebhookConfig([
            'name' => 'test',
            'signing_secret' => 'test',
            'signature_header_name' => 'test',
            'signature_validator' => DefaultSignatureValidator::class,
            'webhook_profile' => ProcessEverythingWebhookProfile::class,
            'webhook_response' => null,
            'webhook_model' => WebhookCall::class,
            'store_headers' => [],
            'process_webhook_job' => ProcessWebhookJobTestClass::class
        ]);

        $webhookCall = WebhookCall::storeWebhook($config, $request);

        $this->assertEquals($url, $webhookCall->url);
    }

    /** @test */
    public function it_truncates_url_when_exceeding_255_characters()
    {
        $baseUrl = 'https://example.com/webhook?';
        $params = [];
        for ($i = 0; $i < 100; $i++) {
            $params[] = "param{$i}=" . str_repeat('x', 10);
        }
        $longUrl = $baseUrl . implode('&', $params);
        $request = Request::create($longUrl);
        $config = new WebhookConfig([
            'name' => 'test',
            'signing_secret' => 'test',
            'signature_header_name' => 'test',
            'signature_validator' => DefaultSignatureValidator::class,
            'webhook_profile' => ProcessEverythingWebhookProfile::class,
            'webhook_response' => null,
            'webhook_model' => WebhookCall::class,
            'store_headers' => [],
            'process_webhook_job' => ProcessWebhookJobTestClass::class
        ]);

        $webhookCall = WebhookCall::storeWebhook($config, $request);

        $this->assertLessThanOrEqual(255, strlen($webhookCall->url));
        $this->assertStringEndsWith('...', $webhookCall->url);
    }
} 