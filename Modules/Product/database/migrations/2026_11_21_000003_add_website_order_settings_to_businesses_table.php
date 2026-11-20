<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('delivery_fee_enabled')->default(false);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->boolean('inquiry_sms_enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['delivery_fee_enabled', 'delivery_fee', 'inquiry_sms_enabled']);
        });
    }
};
