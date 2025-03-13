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
        Schema::table('request_taxis', function (Blueprint $table) {
            $table->foreignId('taxi_id')->constrained('taxis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_taxis', function (Blueprint $table) {
            $table->dropForeign(['taxi_id']);
            $table->dropColumn('taxi_id');
        });
    }
};
