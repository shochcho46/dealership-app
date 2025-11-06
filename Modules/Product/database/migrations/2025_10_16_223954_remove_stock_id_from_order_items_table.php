<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if stock_id column exists first
        if (Schema::hasColumn('order_items', 'stock_id')) {
            // Get foreign key constraints
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'order_items'
                AND COLUMN_NAME = 'stock_id'
                AND CONSTRAINT_NAME != 'PRIMARY'
            ");

            Schema::table('order_items', function (Blueprint $table) use ($foreignKeys) {
                // Drop foreign key if it exists
                foreach ($foreignKeys as $fk) {
                    try {
                        $table->dropForeign($fk->CONSTRAINT_NAME);
                    } catch (\Exception $e) {
                        // Continue if foreign key doesn't exist
                    }
                }
                $table->dropColumn('stock_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
        });
    }
};
