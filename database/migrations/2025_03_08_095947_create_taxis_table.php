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
        Schema::create('taxis', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('engine_capacity');
            $table->decimal('kmpl', 5, 2);  // Kilometers per liter
            $table->string('fuel_type');
            $table->string('gear_type');
            $table->integer('passenger_count');
            $table->decimal('cost_per_day', 10, 2);
            $table->text('description');
            $table->string('display_image')->nullable();  // Main display image
            $table->string('image1')->nullable();  // Additional image 1
            $table->string('image2')->nullable();  // Additional image 2
            $table->string('image3')->nullable();  // Additional image 3
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxis');
    }
};
