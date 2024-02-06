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
        Schema::create('route_stop', function (Blueprint $table) {
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('stop_id');

            $table->unsignedInteger('sequence');
            $table->unsignedInteger('fare')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_stop');
    }
};
