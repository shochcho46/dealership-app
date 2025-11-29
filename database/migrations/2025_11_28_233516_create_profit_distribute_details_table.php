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
        Schema::create('profit_distribute_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profit_distribute_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->tinyInteger('type')->comment('1=Credit, 2=Debit');
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_distribute_details');
    }
};
