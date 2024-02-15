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
        Schema::create('interchange', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Route::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Route::class)->constrained()->cascadeOnDelete();

            $table->foreignIdFor(\App\Models\Stop::class)->constrained()->cascadeOnDelete();


            $table->integer('validity_minutes')->nullable();
            $table->integer('discount_max')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interchange');
    }
};
