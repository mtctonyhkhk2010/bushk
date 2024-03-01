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
        Schema::table('service_times', function (Blueprint $table) {
            $table->renameColumn('weekday', 'weekday_tc');
            $table->string('weekday_en')->after('weekday_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_times', function (Blueprint $table) {
            //
        });
    }
};
