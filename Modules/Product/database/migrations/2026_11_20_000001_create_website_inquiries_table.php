<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('shop_name')->nullable();
            $table->string('mobile', 30);
            $table->string('area')->nullable();
            $table->text('message')->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->json('items')->nullable();
            $table->unsignedTinyInteger('status')->default(0); // 0 new, 1 contacted, 2 closed
            $table->foreignId('handled_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // Permission for the admin "Website Inquiries" page.
        if (Schema::hasTable('permissions')
            && ! DB::table('permissions')->where('name', 'website_inquiry')->where('guard_name', 'admin')->exists()) {
            DB::table('permissions')->insert([
                'name'       => 'website_inquiry',
                'guard_name' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('website_inquiries');
        DB::table('permissions')->where('name', 'website_inquiry')->where('guard_name', 'admin')->delete();
    }
};
