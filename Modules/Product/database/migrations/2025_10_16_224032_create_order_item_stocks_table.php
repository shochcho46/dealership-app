<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_item_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orderitem_id');
            $table->unsignedBigInteger('stock_id');
            $table->integer('quantity');
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('sell_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('actual_profit', 10, 2)->default(0);
            $table->integer('return_quantity')->default(0);
            $table->integer('damage_quantity')->default(0);
            $table->integer('lost_quantity')->default(0);
            $table->timestamps();

            $table->foreign('orderitem_id')->references('id')->on('order_items')->onDelete('cascade');
            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_stocks');
    }
};
