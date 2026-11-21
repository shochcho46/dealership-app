<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Permission for Financial → Commission. */
    public function up(): void
    {
        if (Schema::hasTable('permissions')
            && ! DB::table('permissions')->where('name', 'commission_list')->where('guard_name', 'admin')->exists()) {
            DB::table('permissions')->insert([
                'name'       => 'commission_list',
                'guard_name' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        DB::table('permissions')->where('name', 'commission_list')->where('guard_name', 'admin')->delete();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
