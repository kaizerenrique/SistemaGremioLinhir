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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();

            // Puede estar ligada a una CTA (si viene de pagar_actividad) o ser manual
            $table->foreignId('cta_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->dateTime('start_time');
            $table->dateTime('end_time');

            $table->unsignedBigInteger('liquid_silver')->default(0);
            $table->unsignedBigInteger('items_value')->default(0);

            // Tasas aplicadas (porcentajes)
            $table->decimal('guild_tax_rate', 5, 2)->default(0);
            $table->decimal('management_tax_rate', 5, 2)->default(0);

            // Montos calculados al liquidar
            $table->unsignedBigInteger('guild_tax_amount')->default(0);
            $table->unsignedBigInteger('management_tax_amount')->default(0);
            $table->unsignedBigInteger('total_distributed')->default(0);

            $table->boolean('pro_guild')->default(false);

            $table->string('registered_by_discord_id');
            $table->string('registered_by_name');

            $table->timestamps();

            $table->index('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
