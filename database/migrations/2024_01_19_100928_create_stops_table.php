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
        Schema::create('stops', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('route_id');
            $table->foreign('route_id')->references('id')->on('routes')->cascadeOnDelete();

            $table->string('stop_id');
            $table->unsignedInteger('sequence');

            $table->string('name_tc');
            $table->string('name_en');
            $table->string('name_sc');

            $table->point('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stops');
    }
};
