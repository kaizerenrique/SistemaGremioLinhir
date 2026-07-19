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
        Schema::create('items', function (Blueprint $table) {
            $table->string('unique_name')->primary();
            $table->string('item_type')->nullable();
            $table->integer('tier')->nullable();
            $table->integer('enchantment_level')->default(0);
            $table->string('sprite_name')->nullable();
            $table->json('localized_names')->nullable();
            $table->json('localized_descriptions')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            $table->index('item_type');
            $table->index('tier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
