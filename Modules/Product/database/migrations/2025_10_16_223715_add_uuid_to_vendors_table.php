<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('vendors', 'uuid')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });
        }
        
        // Update existing records with UUID
        DB::table('vendors')->whereNull('uuid')->orWhere('uuid', '')->update([
            'uuid' => DB::raw('UUID()')
        ]);
        
        // Now make it unique if not already
        if (!Schema::hasIndex('vendors', 'vendors_uuid_unique')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->unique('uuid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
