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
        Schema::create('activity_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();

            $table->string('discord_user_id');
            $table->string('user_name');
            $table->foreignId('personaje_id')->nullable()
                ->constrained('personajes')->nullOnDelete();

            $table->decimal('participation_percent', 6, 2);
            $table->unsignedBigInteger('share')->default(0);

            $table->timestamps();

            $table->unique(['activity_id', 'discord_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_participants');
    }
};
