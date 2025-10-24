<?php

use App\Models\Admin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Product\Models\OrderStatus;
use Modules\Product\Models\Vendor;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->unique();
            $table->foreignIdFor(Admin::class);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->integer('total_quantity')->default(0);
            $table->decimal('total_discount_amount', 15, 2)->default(0);
            $table->integer('total_return_quantity')->default(0);
            $table->foreignIdFor(OrderStatus::class);
            $table->integer('total_damage_quantity')->default(0);
            $table->integer('total_lost_quantity')->default(0);
            $table->foreignIdFor(Vendor::class);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
