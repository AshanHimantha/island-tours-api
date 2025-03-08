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
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('itinerary')->comment('JSON array of itinerary items');
            $table->text('include')->comment('JSON array of included items');
            $table->text('exclude')->comment('JSON array of excluded items');
            $table->decimal('per_adult_price', 10, 2);
            $table->string('location');
            $table->string('status')->default('available');  // Added status column
            $table->string('display_image');
            $table->string('image1')->nullable();
            $table->string('image2')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};