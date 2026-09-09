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
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('discord_user_id');
            $table->integer('amount'); // positivo o negativo
            $table->string('reason');
            $table->string('performed_by_discord_id');
            $table->timestamp('created_at')->useCurrent(); // No usamos timestamps() para tener solo created_at

            $table->index('discord_user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
