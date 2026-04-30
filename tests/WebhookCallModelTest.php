<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

beforeEach(function () {
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
});

it('can store webhook without files', function () {
    $request = Request::create('/test', 'POST', ['key' => 'value']);

    $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);

    expect($webhookCall)->toBeInstanceOf(WebhookCall::class);
    expect($webhookCall->name)->toBe('test');
    expect($webhookCall->payload)->toBe(['key' => 'value']);
    expect($webhookCall->attachments)->toBeNull();
});

it('can store webhook with single file', function () {
    Storage::fake('local');

    $file = UploadedFile::fake()->create('test.txt', 1, 'text/plain');

    $request = Request::create('/test', 'POST', ['key' => 'value']);
    $request->files->set('document', $file);

    $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);

    expect($webhookCall)->toBeInstanceOf(WebhookCall::class);
    expect($webhookCall->name)->toBe('test');
    expect($webhookCall->payload)->toBe(['key' => 'value']);
    expect($webhookCall->payload)->not->toHaveKey('attachments');
    expect($webhookCall->attachments)->toHaveCount(1);

    $attachment = $webhookCall->attachments[0];
    expect($attachment['originalName'])->toBe('test.txt');
    expect($attachment['mimeType'])->not->toBeEmpty();
    expect($attachment['size'])->toBeGreaterThan(0);
    expect($attachment)->toHaveKey('content');
});

it('can store webhook with multiple files', function () {
    Storage::fake('local');

    $file1 = UploadedFile::fake()->create('test1.txt', 1);
    $file2 = UploadedFile::fake()->create('test2.pdf', 1);

    $request = Request::create('/test', 'POST', ['key' => 'value']);
    $request->files->set('documents', [$file1, $file2]);

    $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);

    expect($webhookCall)->toBeInstanceOf(WebhookCall::class);
    expect($webhookCall->attachments)->toHaveCount(2);

    $attachment1 = $webhookCall->attachments[0];
    expect($attachment1['originalName'])->toBe('test1.txt');
    expect($attachment1['size'])->toBeGreaterThan(0);

    $attachment2 = $webhookCall->attachments[1];
    expect($attachment2['originalName'])->toBe('test2.pdf');
    expect($attachment2['size'])->toBeGreaterThan(0);
});

it('can store webhook with mixed file structure', function () {
    Storage::fake('local');

    $singleFile = UploadedFile::fake()->create('single.txt', 1);
    $multiFile1 = UploadedFile::fake()->create('multi1.txt', 1);
    $multiFile2 = UploadedFile::fake()->create('multi2.txt', 1);

    $request = Request::create('/test', 'POST', ['key' => 'value']);
    $request->files->set('single_document', $singleFile);
    $request->files->set('multiple_documents', [$multiFile1, $multiFile2]);

    $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);

    expect($webhookCall)->toBeInstanceOf(WebhookCall::class);
    expect($webhookCall->attachments)->toHaveCount(3);

    $fileNames = collect($webhookCall->attachments)->pluck('originalName')->toArray();
    expect($fileNames)->toContain('single.txt');
    expect($fileNames)->toContain('multi1.txt');
    expect($fileNames)->toContain('multi2.txt');
});

it('does not overwrite a user-provided attachments key in the payload', function () {
    $request = Request::create('/test', 'POST', [
        'key' => 'value',
        'attachments' => ['user-provided', 'data'],
    ]);

    $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);

    expect($webhookCall->payload['attachments'])->toBe(['user-provided', 'data']);
    expect($webhookCall->attachments)->toBeNull();
});

it('preserves a user-provided attachments key when files are also uploaded', function () {
    Storage::fake('local');

    $file = UploadedFile::fake()->create('test.txt', 1);

    $request = Request::create('/test', 'POST', [
        'attachments' => ['user-provided'],
    ]);
    $request->files->set('document', $file);

    $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);

    expect($webhookCall->payload['attachments'])->toBe(['user-provided']);
    expect($webhookCall->attachments)->toHaveCount(1);
    expect($webhookCall->attachments[0]['originalName'])->toBe('test.txt');
});

it('does not extract files when store_attachments is disabled', function () {
    Storage::fake('local');

    $config = new WebhookConfig([
        'name' => 'test',
        'signing_secret' => 'secret',
        'signature_header_name' => 'Signature',
        'signature_validator' => \Spatie\WebhookClient\SignatureValidator\DefaultSignatureValidator::class,
        'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
        'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
        'webhook_model' => WebhookCall::class,
        'process_webhook_job' => \Spatie\WebhookClient\Tests\TestClasses\ProcessWebhookJobTestClass::class,
        'store_headers' => [],
        'store_attachments' => false,
    ]);

    $file = UploadedFile::fake()->create('test.txt', 1);
    $request = Request::create('/test', 'POST', ['key' => 'value']);
    $request->files->set('document', $file);

    $webhookCall = WebhookCall::storeWebhook($config, $request);

    expect($webhookCall->attachments)->toBeNull();
    expect($webhookCall->payload)->not->toHaveKey('attachments');
});

it('can retrieve attachments as uploaded file objects', function () {
    Storage::fake('local');

    $file = UploadedFile::fake()->create('test.txt', 1);

    $request = Request::create('/test', 'POST', ['key' => 'value']);
    $request->files->set('document', $file);

    $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);
    $attachments = $webhookCall->getAttachments();

    expect($attachments)->toHaveCount(1);
    expect($attachments[0])->toBeInstanceOf(UploadedFile::class);
    expect($attachments[0]->getClientOriginalName())->toBe('test.txt');
    expect($attachments[0]->getMimeType())->not->toBeEmpty();
});

it('returns empty array when no attachments', function () {
    $request = Request::create('/test', 'POST', ['key' => 'value']);

    $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);
    $attachments = $webhookCall->getAttachments();

    expect($attachments)->toBeArray();
    expect($attachments)->toBeEmpty();
});

it('falls back to payload attachments for rows written by older versions', function () {
    $legacyAttachment = [
        'originalName' => 'legacy.txt',
        'mimeType' => 'text/plain',
        'size' => 7,
        'error' => 0,
        'path' => '/tmp/legacy',
        'content' => base64_encode('legacy!'),
    ];

    $webhookCall = WebhookCall::create([
        'name' => 'test',
        'url' => 'http://example.test/webhook',
        'headers' => [],
        'payload' => ['key' => 'value', 'attachments' => [$legacyAttachment]],
        'attachments' => null,
        'exception' => null,
    ]);

    $attachments = $webhookCall->getAttachments();

    expect($attachments)->toHaveCount(1);
    expect($attachments[0])->toBeInstanceOf(UploadedFile::class);
    expect($attachments[0]->getClientOriginalName())->toBe('legacy.txt');
    expect(file_get_contents($attachments[0]->getPathname()))->toBe('legacy!');
});

it('prefers the attachments column over a payload attachments key when both exist', function () {
    $columnAttachment = [
        'originalName' => 'column.txt',
        'mimeType' => 'text/plain',
        'size' => 6,
        'error' => 0,
        'path' => '/tmp/column',
        'content' => base64_encode('column'),
    ];

    $webhookCall = WebhookCall::create([
        'name' => 'test',
        'url' => 'http://example.test/webhook',
        'headers' => [],
        'payload' => ['attachments' => ['user-provided']],
        'attachments' => [$columnAttachment],
        'exception' => null,
    ]);

    $attachments = $webhookCall->getAttachments();

    expect($attachments)->toHaveCount(1);
    expect($attachments[0]->getClientOriginalName())->toBe('column.txt');
});

it('can retrieve multiple attachments as uploaded file objects', function () {
    Storage::fake('local');

    $file1 = UploadedFile::fake()->create('test1.txt', 1);
    $file2 = UploadedFile::fake()->create('test2.pdf', 1);

    $request = Request::create('/test', 'POST', ['key' => 'value']);
    $request->files->set('documents', [$file1, $file2]);

    $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);
    $attachments = $webhookCall->getAttachments();

    expect($attachments)->toHaveCount(2);
    expect($attachments[0])->toBeInstanceOf(UploadedFile::class);
    expect($attachments[1])->toBeInstanceOf(UploadedFile::class);

    expect($attachments[0]->getClientOriginalName())->toBe('test1.txt');
    expect($attachments[0]->getMimeType())->not->toBeEmpty();

    expect($attachments[1]->getClientOriginalName())->toBe('test2.pdf');
    expect($attachments[1]->getMimeType())->not->toBeEmpty();
});

it('preserves file content through storage and retrieval', function () {
    Storage::fake('local');

    $originalContent = 'This is test content for the file.';
    $file = UploadedFile::fake()->createWithContent('test.txt', $originalContent);

    $request = Request::create('/test', 'POST', ['key' => 'value']);
    $request->files->set('document', $file);

    $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);
    $attachments = $webhookCall->getAttachments();

    expect($attachments)->toHaveCount(1);
    $retrievedContent = file_get_contents($attachments[0]->getPathname());
    expect($retrievedContent)->toBe($originalContent);
});

test('build payload from request method works correctly', function () {
    $request = Request::create('/test', 'POST', ['key' => 'value', 'nested' => ['data' => 'test']]);

    $reflection = new \ReflectionClass(WebhookCall::class);
    $method = $reflection->getMethod('buildPayloadFromRequest');
    $method->setAccessible(true);

    $payload = $method->invokeArgs(null, [$request]);

    expect($payload)->toBe(['key' => 'value', 'nested' => ['data' => 'test']]);
});

test('process request files method handles single file correctly', function () {
    Storage::fake('local');

    $file = UploadedFile::fake()->create('test.txt', 1);
    $files = ['document' => $file];

    $reflection = new \ReflectionClass(WebhookCall::class);
    $method = $reflection->getMethod('processRequestFiles');
    $method->setAccessible(true);

    $result = $method->invokeArgs(null, [$files]);

    expect($result)->toHaveCount(1);
    expect($result[0]['originalName'])->toBe('test.txt');
    expect($result[0]['mimeType'])->toBe('text/plain');
});

test('process request files method handles array of files correctly', function () {
    Storage::fake('local');

    $file1 = UploadedFile::fake()->create('test1.txt', 1);
    $file2 = UploadedFile::fake()->create('test2.txt', 1);
    $files = ['documents' => [$file1, $file2]];

    $reflection = new \ReflectionClass(WebhookCall::class);
    $method = $reflection->getMethod('processRequestFiles');
    $method->setAccessible(true);

    $result = $method->invokeArgs(null, [$files]);

    expect($result)->toHaveCount(2);
    expect($result[0]['originalName'])->toBe('test1.txt');
    expect($result[1]['originalName'])->toBe('test2.txt');
});

it('stores a webhook call when the attachments column does not exist', function () {
    Schema::table('webhook_calls', function (Blueprint $table) {
        $table->dropColumn('attachments');
    });

    $request = Request::create('/test', 'POST', ['key' => 'value']);
    $webhookCall = WebhookCall::storeWebhook($this->webhookConfig, $request);

    expect($webhookCall->exists)->toBeTrue();
    expect($webhookCall->name)->toBe('test');
});
