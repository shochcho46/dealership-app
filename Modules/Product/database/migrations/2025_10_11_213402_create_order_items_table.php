<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Product\Models\Order;
use Modules\Product\Models\Product;
use Modules\Product\Models\Stock;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class);
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(Stock::class);
            $table->integer('quantity');
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('sell_price', 10, 2);
            $table->decimal('total_price', 15, 2);
            $table->decimal('discount_price', 15, 2)->default(0);
            $table->integer('return_quantity')->default(0);
            $table->integer('damage_quantity')->default(0);
            $table->integer('lost_quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
