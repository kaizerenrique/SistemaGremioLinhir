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
        Schema::table('cta_participants', function (Blueprint $table) {
            // Segundos acumulados en el canal de voz
            $table->unsignedInteger('voice_seconds')->default(0)->after('participation_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cta_participants', function (Blueprint $table) {
            $table->dropColumn('voice_seconds');
        });
    }
};
