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
            $table->foreignId('from_route_id')->references('id')->on('routes');
            $table->foreignId('to_route_id')->references('id')->on('routes');

            //if null, thats means can change anywhere
            $table->foreignIdFor(\App\Models\Stop::class)->nullable();

            $table->integer('validity_minutes')->nullable();
            $table->string('discount_mode')->nullable();
            $table->integer('discount')->nullable();
            $table->string('detail')->nullable();
            $table->integer('success_cnt')->nullable();

            $table->string('spec_remark_en')->nullable();
            $table->string('spec_remark_tc')->nullable();
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
