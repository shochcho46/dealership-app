<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Per single unit (piece). Website shows web_price; MRP - web_price = discount.
            $table->decimal('mrp', 10, 2)->nullable()->after('discount_amount');
            $table->decimal('web_price', 10, 2)->nullable()->after('mrp');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['mrp', 'web_price']);
        });
    }
};
