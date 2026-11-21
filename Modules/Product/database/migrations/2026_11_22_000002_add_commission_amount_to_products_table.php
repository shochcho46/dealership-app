<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Per-piece price that SR commission is calculated against (null = use the stock sell price). */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'commission_amount')) {
                $table->decimal('commission_amount', 10, 2)->nullable()->after('web_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'commission_amount')) {
                $table->dropColumn('commission_amount');
            }
        });
    }
};
