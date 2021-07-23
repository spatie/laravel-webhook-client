## From v2 to v3

- add a migration to add a `headers` column to the `webhook_calls` table.
  
```php
$table->json('headers')->nullable();`
```

- add a key `store_headers` to each entry in `configs` of the `webhook-client` config file. See the default config file for an example.
