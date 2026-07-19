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
        Schema::create('kill_event_equipment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kill_event_id');
            $table->enum('player_role', ['killer', 'victim']);
            $table->string('slot'); // MainHand, Head, Armor, etc.
            $table->string('item_type'); // uniqueName del ítem
            $table->integer('count')->default(1);
            $table->integer('quality')->default(0);
            $table->timestamps();

            $table->foreign('kill_event_id')
                  ->references('id')
                  ->on('kill_events')
                  ->onDelete('cascade');

            // Índices para búsquedas rápidas
            $table->index('kill_event_id');
            $table->index('item_type');
            $table->index('player_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kill_event_equipment');
    }
};
