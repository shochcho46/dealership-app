<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->foreignId('route_plan_id')
                ->nullable()
                ->after('country_id')
                ->constrained('route_plans')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['route_plan_id']);
            $table->dropColumn('route_plan_id');
        });
    }
};
