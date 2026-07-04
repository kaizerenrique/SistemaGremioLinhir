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
        Schema::create('edificios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propiedad_id')->constrained('propiedades')->onDelete('cascade');            
            $table->integer('numero_edificio'); // Número del edificio dentro de la propiedad            
            $table->string('nombre')->nullable(); // Nombre personalizado del edificio
            // Tipo de edificio
            $table->enum('tipo', [
                'CASA',
                'Crafteo', 
                'Refinamiento',
                'OTRO'
            ])->default('CASA');            
            $table->integer('capacidad_max_cofres')->default(13); // Capacidad máxima de cofres (normalmente 14, pero usan 13)
            // Coordenadas o posición dentro de la propiedad
            $table->string('coordenadas')->nullable()->comment('Posición relativa en el HO');
            $table->text('descripcion_posicion')->nullable();
            // Metadatos
            $table->boolean('activo')->default(true);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edificios');
    }
};
