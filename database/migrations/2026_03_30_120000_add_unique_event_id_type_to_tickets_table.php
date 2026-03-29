<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prevents more than one ticket row per event and type combination.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->unique(['event_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique(['event_id', 'type']);
        });
    }
};
