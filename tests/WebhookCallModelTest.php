<?php

namespace Spatie\WebhookClient\Tests;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

class WebhookCallModelTest extends TestCase
{
    protected WebhookConfig $webhookConfig;

    public function setUp(): void
    {
        parent::setUp();

        $this->webhookConfig = new WebhookConfig([
            'name' => 'test',
            'signing_secret' => 'secret',
            'signature_header_name' => 'Signature',
            'signature_validator' => \Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => WebhookCall::class,
            'process_webhook_job' => \Spatie\WebhookClient\Tests\TestClasses\ProcessWebhookJobTestClass::class,
            'store_headers' => [],
        ]);
    }

    public function test_it_can_store_webhook_without_files()
    {
        $request = Request::create('/test', 'POST', ['key' => 'value']);

        $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);

        $this->assertInstanceOf(WebhookCall::class, $webhookCall);
        $this->assertEquals('test', $webhookCall->name);
        $this->assertEquals(['key' => 'value'], $webhookCall->payload);
        $this->assertArrayNotHasKey('attachments', $webhookCall->payload);
    }

    public function test_it_can_store_webhook_with_single_file()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.txt', 1, 'text/plain');

        $request = Request::create('/test', 'POST', ['key' => 'value']);
        $request->files->set('document', $file);

        $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);

        $this->assertInstanceOf(WebhookCall::class, $webhookCall);
        $this->assertEquals('test', $webhookCall->name);
        $this->assertEquals('value', $webhookCall->payload['key']);
        $this->assertArrayHasKey('attachments', $webhookCall->payload);
        $this->assertCount(1, $webhookCall->payload['attachments']);

        $attachment = $webhookCall->payload['attachments'][0];
        $this->assertEquals('test.txt', $attachment['originalName']);
        $this->assertNotEmpty($attachment['mimeType']);
        $this->assertGreaterThan(0, $attachment['size']);
        $this->assertArrayHasKey('content', $attachment);
    }

    public function test_it_can_store_webhook_with_multiple_files()
    {
        Storage::fake('local');

        $file1 = UploadedFile::fake()->create('test1.txt', 1);
        $file2 = UploadedFile::fake()->create('test2.pdf', 1);

        $request = Request::create('/test', 'POST', ['key' => 'value']);
        $request->files->set('documents', [$file1, $file2]);

        $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);

        $this->assertInstanceOf(WebhookCall::class, $webhookCall);
        $this->assertArrayHasKey('attachments', $webhookCall->payload);
        $this->assertCount(2, $webhookCall->payload['attachments']);

        $attachment1 = $webhookCall->payload['attachments'][0];
        $this->assertEquals('test1.txt', $attachment1['originalName']);
        $this->assertGreaterThan(0, $attachment1['size']);

        $attachment2 = $webhookCall->payload['attachments'][1];
        $this->assertEquals('test2.pdf', $attachment2['originalName']);
        $this->assertGreaterThan(0, $attachment2['size']);
    }

    public function test_it_can_store_webhook_with_mixed_file_structure()
    {
        Storage::fake('local');

        $singleFile = UploadedFile::fake()->create('single.txt', 1);
        $multiFile1 = UploadedFile::fake()->create('multi1.txt', 1);
        $multiFile2 = UploadedFile::fake()->create('multi2.txt', 1);

        $request = Request::create('/test', 'POST', ['key' => 'value']);
        $request->files->set('single_document', $singleFile);
        $request->files->set('multiple_documents', [$multiFile1, $multiFile2]);

        $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);

        $this->assertInstanceOf(WebhookCall::class, $webhookCall);
        $this->assertArrayHasKey('attachments', $webhookCall->payload);
        $this->assertCount(3, $webhookCall->payload['attachments']);

        $fileNames = collect($webhookCall->payload['attachments'])->pluck('originalName')->toArray();
        $this->assertContains('single.txt', $fileNames);
        $this->assertContains('multi1.txt', $fileNames);
        $this->assertContains('multi2.txt', $fileNames);
    }

    public function test_it_can_retrieve_attachments_as_uploaded_file_objects()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.txt', 1);

        $request = Request::create('/test', 'POST', ['key' => 'value']);
        $request->files->set('document', $file);

        $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);
        $attachments = $webhookCall->getAttachments();

        $this->assertCount(1, $attachments);
        $this->assertInstanceOf(UploadedFile::class, $attachments[0]);
        $this->assertEquals('test.txt', $attachments[0]->getClientOriginalName());
        $this->assertNotEmpty($attachments[0]->getMimeType());
    }

    public function test_it_returns_empty_array_when_no_attachments()
    {
        $request = Request::create('/test', 'POST', ['key' => 'value']);

        $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);
        $attachments = $webhookCall->getAttachments();

        $this->assertIsArray($attachments);
        $this->assertEmpty($attachments);
    }

    public function test_it_can_retrieve_multiple_attachments_as_uploaded_file_objects()
    {
        Storage::fake('local');

        $file1 = UploadedFile::fake()->create('test1.txt', 1);
        $file2 = UploadedFile::fake()->create('test2.pdf', 1);

        $request = Request::create('/test', 'POST', ['key' => 'value']);
        $request->files->set('documents', [$file1, $file2]);

        $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);
        $attachments = $webhookCall->getAttachments();

        $this->assertCount(2, $attachments);
        $this->assertInstanceOf(UploadedFile::class, $attachments[0]);
        $this->assertInstanceOf(UploadedFile::class, $attachments[1]);

        $this->assertEquals('test1.txt', $attachments[0]->getClientOriginalName());
        $this->assertNotEmpty($attachments[0]->getMimeType());

        $this->assertEquals('test2.pdf', $attachments[1]->getClientOriginalName());
        $this->assertNotEmpty($attachments[1]->getMimeType());
    }

    public function test_it_preserves_file_content_through_storage_and_retrieval()
    {
        Storage::fake('local');

        $originalContent = 'This is test content for the file.';
        $file = UploadedFile::fake()->createWithContent('test.txt', $originalContent);

        $request = Request::create('/test', 'POST', ['key' => 'value']);
        $request->files->set('document', $file);

        $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);
        $attachments = $webhookCall->getAttachments();

        $this->assertCount(1, $attachments);
        $retrievedContent = file_get_contents($attachments[0]->getPathname());
        $this->assertEquals($originalContent, $retrievedContent);
    }

    public function test_build_payload_from_request_method_works_correctly()
    {
        $request = Request::create('/test', 'POST', ['key' => 'value', 'nested' => ['data' => 'test']]);

        $reflection = new \ReflectionClass(WebhookCall::class);
        $method = $reflection->getMethod('buildPayloadFromRequest');
        $method->setAccessible(true);

        $payload = $method->invokeArgs(null, [$request]);

        $this->assertEquals(['key' => 'value', 'nested' => ['data' => 'test']], $payload);
    }

    public function test_process_request_files_method_handles_single_file_correctly()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.txt', 1);
        $files = ['document' => $file];

        $reflection = new \ReflectionClass(WebhookCall::class);
        $method = $reflection->getMethod('processRequestFiles');
        $method->setAccessible(true);

        $result = $method->invokeArgs(null, [$files]);

        $this->assertCount(1, $result);
        $this->assertEquals('test.txt', $result[0]['originalName']);
        $this->assertEquals('text/plain', $result[0]['mimeType']);
    }

    public function test_process_request_files_method_handles_array_of_files_correctly()
    {
        Storage::fake('local');

        $file1 = UploadedFile::fake()->create('test1.txt', 1);
        $file2 = UploadedFile::fake()->create('test2.txt', 1);
        $files = ['documents' => [$file1, $file2]];

        $reflection = new \ReflectionClass(WebhookCall::class);
        $method = $reflection->getMethod('processRequestFiles');
        $method->setAccessible(true);

        $result = $method->invokeArgs(null, [$files]);

        $this->assertCount(2, $result);
        $this->assertEquals('test1.txt', $result[0]['originalName']);
        $this->assertEquals('test2.txt', $result[1]['originalName']);
    }
}
