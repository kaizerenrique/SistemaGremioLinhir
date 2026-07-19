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
        Schema::create('battle_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->string('player_id');
            $table->string('player_name');
            $table->string('guild_id')->nullable();
            $table->string('alliance_id')->nullable();
            $table->integer('kills')->default(0);
            $table->integer('deaths')->default(0);
            $table->bigInteger('kill_fame')->default(0);
            $table->float('average_item_power', 8, 2)->default(0);
            $table->float('damage_done', 12, 2)->default(0);
            $table->float('support_healing_done', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['battle_id', 'player_id']);
            $table->foreign('battle_id')->references('id')->on('battles')->onDelete('cascade');
            $table->foreign('guild_id')->references('id')->on('guilds')->onDelete('set null');
            $table->foreign('alliance_id')->references('id')->on('alliances')->onDelete('set null');

            $table->index('player_id');
            $table->index('guild_id');
            $table->index('alliance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battle_participants');
    }
};
