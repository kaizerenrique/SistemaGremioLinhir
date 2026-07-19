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
        Schema::create('battles', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // ID de la batalla (de la API)
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamp('timeout')->nullable();
            $table->bigInteger('total_fame')->default(0);
            $table->integer('total_kills')->default(0);
            $table->string('cluster_name')->nullable();
            $table->timestamps();

            $table->index('start_time');
            $table->index('total_fame');
            $table->index('total_kills');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battles');
    }
};
