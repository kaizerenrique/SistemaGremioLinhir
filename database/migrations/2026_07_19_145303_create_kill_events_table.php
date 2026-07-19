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
        Schema::create('kill_events', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // EventId
            $table->unsignedBigInteger('battle_id');
            $table->timestamp('timestamp')->nullable();
            // Asesino
            $table->string('killer_id');
            $table->string('killer_name');
            $table->string('killer_guild_id')->nullable();
            $table->string('killer_alliance_id')->nullable();
            $table->float('killer_avg_ip', 8, 2)->default(0);
            $table->bigInteger('killer_kill_fame')->default(0);
            // Víctima
            $table->string('victim_id');
            $table->string('victim_name');
            $table->string('victim_guild_id')->nullable();
            $table->string('victim_alliance_id')->nullable();
            $table->float('victim_avg_ip', 8, 2)->default(0);
            // Metadatos
            $table->integer('number_of_participants')->default(0);
            $table->integer('group_member_count')->default(0);
            $table->string('kill_area')->nullable();
            $table->timestamps();

            $table->foreign('battle_id')->references('id')->on('battles')->onDelete('cascade');
            $table->foreign('killer_guild_id')->references('id')->on('guilds')->onDelete('set null');
            $table->foreign('killer_alliance_id')->references('id')->on('alliances')->onDelete('set null');
            $table->foreign('victim_guild_id')->references('id')->on('guilds')->onDelete('set null');
            $table->foreign('victim_alliance_id')->references('id')->on('alliances')->onDelete('set null');

            $table->index('battle_id');
            $table->index('killer_id');
            $table->index('victim_id');
            $table->index('timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kill_events');
    }
};
