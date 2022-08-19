## From v2 to v3

#### Create Migration
```bash
php artisan make:migration add_columns_to_webhook_calls
```

###  Here's an example how your migration should look.
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToWebhookCalls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('webhook_calls', function (Blueprint $table) {
            $table->string('url')->nullable();
            $table->json('headers')->nullable();
            $table->json('payload')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('webhook_calls', function (Blueprint $table) {
            $table->dropColumn('url');
            $table->dropColumn('headers');
            $table->text('payload')->change();
        });
    }
}
```

- add a key `store_headers` to each entry in `configs` of the `webhook-client` config file. See the default config file for an example.

- the `Spatie\WebhookClient\Events\InvalidSignature` event has been renamed to `Spatie\WebhookClient\Events\InvalidWebhookSignatureEvent`

- the `Spatie\WebhookClient\ProcessWebhookJob` job has been moved to `Spatie\WebhookClient\Jobs\ProcessWebhookJob`
