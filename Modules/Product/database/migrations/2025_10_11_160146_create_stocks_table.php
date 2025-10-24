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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('batch_id');
            $table->decimal('purchase_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('total_price', 12, 2);
            $table->decimal('sell_price', 10, 2);
            $table->integer('damage_quantity')->default(0);
            $table->integer('sold_quantity')->default(0);
            $table->integer('stolen_quantity')->default(0);
            $table->integer('transfer_quantity')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
