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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Required
            $table->unsignedBigInteger('color_id')->nullable();
            $table->string('measurement_unit_name')->nullable();
            $table->string('measurement_unit_number')->nullable();
            $table->string('package_unit_name')->nullable();
            $table->string('package_unit_quantity')->nullable();
            $table->unsignedBigInteger('unit_id'); // Required
            $table->boolean('status')->default(1);
            $table->timestamps();

            $table->foreign('color_id')->references('id')->on('colors')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
