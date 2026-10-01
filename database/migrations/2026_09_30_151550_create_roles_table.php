<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Roles que forman parte del sistema y no deben poder
     * editarse ni eliminarse desde la UI.
     */
    private array $systemRoles = [
        'Administrador',
        'Oficial',
        'Sub-Oficial',
        'Linhir',
        'Usuario',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ---------------------------------------------------------
        // 1. Añadir columna is_system
        // ---------------------------------------------------------
        Schema::table('roles', function (Blueprint $table) {
            // after() es ignorado por SQLite pero respetado por MySQL/MariaDB
            $table->boolean('is_system')
                ->default(false)
                ->after('guard_name');
        });

        // ---------------------------------------------------------
        // 2. Marcar los roles base como sistema (si existen)
        // ---------------------------------------------------------
        DB::table('roles')
            ->whereIn('name', $this->systemRoles)
            ->update(['is_system' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
