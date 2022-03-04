## From v2 to v3

- add a migration to add `url` and `headers` columns to the `webhook_calls` table.
  
```php
$table->string('url')->nullable();
$table->json('headers')->nullable();
```

- add a key `store_headers` to each entry in `configs` of the `webhook-client` config file. See the default config file for an example.

- the `Spatie\WebhookClient\Events\InvalidSignature` event has been renamed to `Spatie\WebhookClient\Events\InvalidWebhookSignatureEvent`

- the `Spatie\WebhookClient\ProcessWebhookJob` job has been moved to `Spatie\WebhookClient\Jobs\ProcessWebhookJob`
