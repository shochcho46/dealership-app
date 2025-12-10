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
        Schema::table('company_order_items', function (Blueprint $table) {
            $table->integer('damage_quantity')->default(0)->after('quantity');
            $table->integer('lost_quantity')->default(0)->after('damage_quantity');
            $table->decimal('damage_price', 15, 2)->default(0)->after('lost_quantity');
            $table->decimal('lost_price', 15, 2)->default(0)->after('damage_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_order_items', function (Blueprint $table) {
            $table->dropColumn(['damage_quantity', 'lost_quantity', 'damage_price', 'lost_price']);
        });
    }
};
