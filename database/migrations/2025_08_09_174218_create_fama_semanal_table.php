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
        Schema::create('fama_semanal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personaje_id')->constrained('personajes')->onDelete('cascade');
            $table->date('semana_inicio')->comment('Fecha de inicio de la semana (lunes)');
            $table->bigInteger('fama_pve_inicio')->default(0)->comment('Fama PvE al inicio de la semana');
            $table->bigInteger('fama_pvp_inicio')->default(0)->comment('Fama PvP al inicio de la semana');
            $table->bigInteger('fama_pve_fin')->default(0)->comment('Fama PvE al final de la semana');
            $table->bigInteger('fama_pvp_fin')->default(0)->comment('Fama PvP al final de la semana');
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['personaje_id', 'semana_inicio']);
            
            // Índices para búsquedas eficientes
            $table->index('semana_inicio');
            $table->index('personaje_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fama_semanal');
    }
};
