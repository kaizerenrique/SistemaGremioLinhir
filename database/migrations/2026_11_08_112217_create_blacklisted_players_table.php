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
        Schema::create('blacklisted_players', function (Blueprint $table) {
            $table->id();
            
            // Identificadores
            $table->string('albion_player_id')->unique();
            $table->string('albion_player_name');
            $table->string('discord_user_id')->nullable(); // Opcional, por si lo tenemos
            
            // Motivo y Auditoría
            $table->text('reason');
            $table->string('banned_by_name');
            $table->string('banned_by_discord_id');
            
            $table->timestamps();
            
            $table->index('albion_player_name');
            $table->index('discord_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blacklisted_players');
    }
};
