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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_number')->unique();
            $table->date('inspection_date');
            $table->text('notes')->nullable();
            $table->decimal('total_damage_amount', 15, 2)->default(0);
            $table->decimal('total_lost_amount', 15, 2)->default(0);
            $table->integer('total_damage_qty')->default(0);
            $table->integer('total_lost_qty')->default(0);
            $table->unsignedBigInteger('inspected_by')->nullable();
            $table->timestamps();

            $table->foreign('inspected_by')->references('id')->on('admins')->onDelete('set null');
        });

        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('inspections')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('system_qty')->default(0);
            $table->integer('physical_qty')->default(0);
            $table->integer('damage_qty')->default(0);
            $table->integer('lost_qty')->default(0);
            $table->decimal('damage_amount', 15, 2)->default(0);
            $table->decimal('lost_amount', 15, 2)->default(0);
            $table->decimal('avg_purchase_price', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_items');
        Schema::dropIfExists('inspections');
    }
};
