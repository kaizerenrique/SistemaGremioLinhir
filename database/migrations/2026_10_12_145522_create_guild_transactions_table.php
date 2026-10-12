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
        Schema::create('guild_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['inicial', 'ingreso', 'egreso']);
            $table->unsignedBigInteger('amount');
            $table->string('reason');
            $table->string('image_url')->nullable();

            $table->string('performed_by_discord_id');
            $table->string('performed_by_name');

            $table->timestamps();

            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guild_transactions');
    }
};
