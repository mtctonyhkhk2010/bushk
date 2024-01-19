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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('company');
            $table->string('name');
            $table->string('bound')->nullable();
            $table->string('service_type')->nullable();
            $table->string('orig_tc');
            $table->string('orig_sc');
            $table->string('orig_en');
            $table->string('dest_tc');
            $table->string('dest_sc');
            $table->string('dest_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
