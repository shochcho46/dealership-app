<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Product\Models\Warehouse;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->integer('froze_quantity')->default(0)->after('transfer_quantity');
            $table->foreignIdFor(Warehouse::class)->nullable()->after('product_id');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn(['froze_quantity', 'warehouse_id']);
        });
    }
};
