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
        Schema::create('especialidades_semanales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personaje_id');
            $table->date('semana_inicio');
            $table->string('tipo', 30); // Ej: 'Hide', 'Rock', 'Crafting_Total', ...
            $table->bigInteger('valor_inicio')->default(0);
            $table->bigInteger('valor_fin')->default(0);
            $table->timestamps();

            $table->unique(['personaje_id', 'semana_inicio', 'tipo']);
            $table->foreign('personaje_id')
                  ->references('id')
                  ->on('personajes')
                  ->onDelete('cascade');

            $table->index('semana_inicio');
            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especialidades_semanales');
    }
};
