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
        Schema::create('mtr_info', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('line_id');
            $table->string('line_name_tc');
            $table->string('line_name_en');
            $table->string('line_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mtr_info');
    }
};
