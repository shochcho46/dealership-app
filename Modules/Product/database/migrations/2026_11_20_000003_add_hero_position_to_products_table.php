<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Slot (1–5) in the public website hero; null = not pinned (slot is auto-filled).
            $table->unsignedTinyInteger('hero_position')->nullable()->after('website_media_id');
        });

        if (Schema::hasTable('permissions')
            && ! DB::table('permissions')->where('name', 'website_settings')->where('guard_name', 'admin')->exists()) {
            DB::table('permissions')->insert([
                'name'       => 'website_settings',
                'guard_name' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('hero_position');
        });
        DB::table('permissions')->where('name', 'website_settings')->where('guard_name', 'admin')->delete();
    }
};
