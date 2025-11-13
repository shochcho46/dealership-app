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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('color_id');
            $table->tinyInteger('discount_type')->nullable()->comment('0 = fixed, 1 = percent')->after('package_unit_quantity');
            $table->double('discount_amount', 10, 2)->default(0)->nullable()->after('discount_type');
            $table->text('description')->nullable()->after('discount_amount');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'discount_type', 'discount_amount', 'description']);
        });
    }
};
