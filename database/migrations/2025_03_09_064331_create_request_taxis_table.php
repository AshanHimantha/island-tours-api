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
        Schema::create('request_taxis', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('identification_number')->comment('NIC or Passport Number');
            $table->string('contact_number');
            $table->string('whatsapp_number')->nullable();
            $table->integer('adult_count');
            $table->integer('kids_count')->default(0);
            $table->date('date_from');
            $table->date('date_to');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_taxis');
    }
};