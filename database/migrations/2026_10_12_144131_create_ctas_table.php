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
        Schema::create('ctas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('composition_id')->constrained();

            $table->string('title');
            $table->string('location');

            // Fechas: guardamos todo en UTC (una sola columna DATETIME cada una)
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('reminder_at')->nullable();
            $table->unsignedSmallInteger('reminder_minutes')->default(10);
            $table->boolean('reminder_sent')->default(false);

            // Estado del ciclo de vida
            $table->enum('status', ['scheduled', 'cancelled', 'completed'])->default('scheduled');
            $table->boolean('paid')->default(false);
            $table->boolean('pro_guild')->default(false);

            // Integración con Discord
            $table->string('discord_message_id')->nullable();
            $table->string('discord_channel_id')->nullable();
            $table->string('discord_event_id')->nullable();
            $table->string('discord_role_id')->nullable();

            // Auditoría
            $table->string('created_by_discord_id');
            $table->string('created_by_name');

            $table->timestamps();

            $table->index('status');
            $table->index(['reminder_sent', 'reminder_at']);
            $table->index('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ctas');
    }
};
