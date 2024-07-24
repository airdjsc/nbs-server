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
            $table->dropColumn('date');
            $table->dropColumn('time');
            $table->renameColumn('date_time', 'utc_date_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function(Blueprint $table) {
            $table->string('date');
            $table->string('time');
            $table->renameColumn('utc_date_time', 'date_time');
        });
    }
};
