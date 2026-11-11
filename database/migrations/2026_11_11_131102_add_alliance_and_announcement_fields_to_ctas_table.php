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
        Schema::table('ctas', function (Blueprint $table) {
            // Visibilidad: 'linhir' (solo gremio) o 'alliance' (HRL)
            $table->string('visibility', 20)->default('linhir')->after('location');
            
            // Tipo de Loot: 'full_split', 'taxed_split', 'pro_guild'
            $table->string('loot_type', 20)->default('taxed_split')->after('visibility');
            
            // Programación del anuncio
            $table->dateTime('announce_at')->nullable()->after('reminder_at');
            $table->boolean('announced')->default(false)->after('announce_at');
            
            // Canal de voz creado por el bot
            $table->string('discord_voice_channel_id', 32)->nullable()->after('discord_channel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ctas', function (Blueprint $table) {
            $table->dropColumn([
                'visibility', 
                'loot_type', 
                'announce_at', 
                'announced', 
                'discord_voice_channel_id'
            ]);
        });
    }
};
