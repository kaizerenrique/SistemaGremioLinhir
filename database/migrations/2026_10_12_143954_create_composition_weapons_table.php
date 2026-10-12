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
        Schema::create('composition_weapons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('composition_id')->constrained()->cascadeOnDelete();
            $table->string('weapon_name');
            $table->string('weapon_icon', 64)->nullable(); // emoji unicode o :nombre: para resolver
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['composition_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('composition_weapons');
    }
};
