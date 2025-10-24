<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Admin;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendor_accounts', function (Blueprint $table) {
            $table->foreignIdFor(Admin::class, 'created_by')->nullable()->after('collection_date')->constrained('admins')->onDelete('set null');
            $table->foreignIdFor(Admin::class, 'deposite_by')->nullable()->after('created_by')->constrained('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_accounts', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['deposite_by']);
            $table->dropColumn(['created_by', 'deposite_by']);
        });
    }
};
