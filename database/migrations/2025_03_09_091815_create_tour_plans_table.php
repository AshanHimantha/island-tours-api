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
        Schema::create('tour_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->date('tour_date');
            $table->string('requester_name');
            $table->string('requester_email');
            $table->string('requester_id_passport');
            $table->string('contact_number');
            $table->string('whatsapp')->nullable();
            $table->integer('adult_count');
            $table->integer('kids_count')->default(0);
            $table->string('country');
            $table->foreignId('vehicle_id')->constrained('taxis')->cascadeOnDelete();
            $table->enum('status', ['pending', 'confirmed', 'canceled', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_plans');
    }
};