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
            //$table->unsignedBigInteger('company_id')->nullable();
            $table->foreignIdFor(\App\Models\Company::class)->nullable();

            $table->string('stop_code')->index();

            $table->string('name_tc');
            $table->string('name_en');

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
