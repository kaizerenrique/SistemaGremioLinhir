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
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('personaje_id')->constrained()->cascadeOnDelete();
            $table->string('discord_user_id');
            $table->string('user_name');

            $table->unsignedBigInteger('amount');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('reviewed_by_discord_id')->nullable();
            $table->string('reviewed_by_name')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('notes')->nullable();

            // FK al movimiento bancario creado al aprobar (auditoría)
            $table->foreignId('banco_gremial_id')->nullable()
                ->constrained('banco_gremials')->nullOnDelete();

            $table->timestamps();

            $table->index('status');
            $table->index('discord_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
