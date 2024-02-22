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
        Schema::create('service_times', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(\App\Models\Route::class)->constrained()->cascadeOnDelete();
            $table->integer('weekday_id');
            $table->string('weekday');
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->integer('frequency_min')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_times');
    }
};
