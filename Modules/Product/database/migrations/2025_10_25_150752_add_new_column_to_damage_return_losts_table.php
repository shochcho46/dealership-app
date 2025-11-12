<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Product\Models\OrderItem;
use Modules\Product\Models\OrderItemStock;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('damage_return_losts', function (Blueprint $table) {
            $table->foreignIdFor(OrderItem::class)->after('stock_id')->nullable();
            $table->foreignIdFor(OrderItemStock::class)->after('order_item_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('damage_return_losts', function (Blueprint $table) {
            $table->dropColumn('order_item_id');
            $table->dropColumn('order_item_stock_id');
        });
    }
};
