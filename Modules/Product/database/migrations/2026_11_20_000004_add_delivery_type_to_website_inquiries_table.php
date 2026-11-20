<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_inquiries', function (Blueprint $table) {
            // Where the customer wants delivery: "shop" or "home". `area` holds the place / address.
            $table->string('delivery_type', 10)->nullable()->after('area');
        });
    }

    public function down(): void
    {
        Schema::table('website_inquiries', function (Blueprint $table) {
            $table->dropColumn('delivery_type');
        });
    }
};
