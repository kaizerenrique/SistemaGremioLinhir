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
        Schema::table('personajes', function (Blueprint $table) {
            $table->foreignId('allied_guild_id')
                ->nullable()
                ->after('GuildId')
                ->constrained('allied_guilds')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personajes', function (Blueprint $table) {
            $table->dropForeign(['allied_guild_id']);
            $table->dropColumn('allied_guild_id');
        });
    }
};
