<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade la columna `active` a composition_weapons.
     *
     * Permite "retirar" armas sin borrarlas físicamente, para no romper
     * las FKs de `cta_participants.composition_weapon_id` cuando ya
     * existen CTAs históricas que las referencian.
     *
     * - active = true  → visible y seleccionable en el editor y nuevas CTAs.
     * - active = false → retirada (invisible en UI), pero conservada para
     *                    mantener el histórico de CTAs pasadas intacto.
     */

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('composition_weapons', function (Blueprint $table) {
            $table->boolean('active')
                ->default(true)
                ->after('sort_order');

            // Índice para acelerar el filtro por composición + active
            $table->index(['composition_id', 'active'], 'comp_weapon_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('composition_weapons', function (Blueprint $table) {
            $table->dropIndex('comp_weapon_active_idx');
            $table->dropColumn('active');
        });
    }
};
