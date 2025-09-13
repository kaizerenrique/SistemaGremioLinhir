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
        Schema::create('banco_gremials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personaje_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto', 12, 2);
            $table->string('concepto');
            $table->string('referencia')->nullable();
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index('personaje_id');
            $table->index('tipo');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banco_gremials');
    }
};
