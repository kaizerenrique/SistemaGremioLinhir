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
        Schema::table('banco_gremials', function (Blueprint $table) {
            // Nullable: movimientos antiguos no lo tienen y "Sistema" es un valor válido.
            $table->string('autorizado_por', 120)
                ->nullable()
                ->after('referencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banco_gremials', function (Blueprint $table) {
            $table->dropColumn('autorizado_por');
        });
    }
};
