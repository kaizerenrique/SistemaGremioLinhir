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
        Schema::create('allied_guilds', function (Blueprint $table) {
            $table->id();

            // Identificación en Albion
            $table->string('guild_id', 64)->unique();
            $table->string('guild_name', 255);
            $table->string('alliance_name', 255)->nullable();

            // Configuración Linhir (nullable hasta que el admin configure)
            $table->string('tag', 8)->nullable()->unique();
            $table->string('discord_role_id', 32)->nullable();
            $table->string('color', 7)->default('#9A8667');

            // Estado
            $table->boolean('active')->default(false);   // ← nace inactivo
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by_discord_id', 32)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allied_guilds');
    }
};
