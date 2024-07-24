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
        Schema::table('appointments', function(Blueprint $table) {
            $table->renameColumn('utc_date_time', 'datetime_utc');
            $table->renameColumn('day', 'day_utc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function(Blueprint $table) {
            $table->renameColumn('datetime_utc', 'utc_date_time');
            $table->renameColumn('day_utc', 'day');
        });
    }
};
