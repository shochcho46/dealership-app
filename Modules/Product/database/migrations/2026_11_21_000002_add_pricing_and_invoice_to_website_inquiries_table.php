<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_inquiries', function (Blueprint $table) {
            $table->decimal('subtotal', 12, 2)->default(0)->after('items');
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('subtotal');
            $table->decimal('total', 12, 2)->default(0)->after('delivery_fee');
            // Random code for the public invoice link /i/{code} (sent by SMS).
            $table->string('invoice_code', 12)->nullable()->unique()->after('total');
            $table->timestamp('contacted_at')->nullable()->after('handled_by');
            $table->timestamp('sms_sent_at')->nullable()->after('contacted_at');
            $table->string('sms_status', 20)->nullable()->after('sms_sent_at'); // sent | failed | disabled
            $table->string('sms_response', 255)->nullable()->after('sms_status');
        });
    }

    public function down(): void
    {
        Schema::table('website_inquiries', function (Blueprint $table) {
            $table->dropUnique(['invoice_code']);
            $table->dropColumn(['subtotal', 'delivery_fee', 'total', 'invoice_code', 'contacted_at', 'sms_sent_at', 'sms_status', 'sms_response']);
        });
    }
};
