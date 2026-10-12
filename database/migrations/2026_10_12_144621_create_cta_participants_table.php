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
        Schema::create('cta_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cta_id')->constrained()->cascadeOnDelete();

            $table->string('discord_user_id');
            $table->string('user_name');

            // FK opcional a personaje (si el usuario está vinculado en la web)
            $table->foreignId('personaje_id')->nullable()
                ->constrained('personajes')->nullOnDelete();

            // Arma escogida de la composición
            $table->foreignId('composition_weapon_id')->nullable()
                ->constrained('composition_weapons');

            $table->enum('status', ['confirmed', 'absent', 'declined'])->default('confirmed');
            $table->decimal('participation_percent', 5, 2)->default(100);

            $table->timestamps();

            // Un usuario = una fila por CTA
            $table->unique(['cta_id', 'discord_user_id']);

            // Índice parcial para unicidad de arma: sólo cuando status='confirmed'
            // Generamos una columna que vale composition_weapon_id sólo si confirmed,
            // y NULL en cualquier otro caso (NULL no colisiona en MySQL).
            $table->unsignedBigInteger('confirmed_weapon_id')
                ->nullable()
                ->storedAs("case when `status` = 'confirmed' then `composition_weapon_id` else null end");

            $table->unique(['cta_id', 'confirmed_weapon_id'], 'cta_weapon_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cta_participants');
    }
};
