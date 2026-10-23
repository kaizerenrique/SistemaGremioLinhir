<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialiteAuthController;
use App\Livewire\Blog\BlogIndex;
use App\Livewire\Blog\BlogShow;

// =========================================================
// RUTAS PÚBLICAS
// =========================================================
Route::get('/', fn() => view('welcome'))->name('welcome');

Route::get('/calculadora-de-cultivos', fn() => view('calculadoradesemillas'))
    ->name('calculadoradesemillas');

Route::get('/auth/redirect', [SocialiteAuthController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/callback', [SocialiteAuthController::class, 'callback'])->name('auth.callback');

Route::get('/sitemap.xml', fn() => response()->file(public_path('sitemap.xml')));

// Blog (público)
Route::get('/blog', BlogIndex::class)->name('blog.index');
Route::get('/blog/{id}', BlogShow::class)->name('blog.show');

// =========================================================
// RUTAS AUTENTICADAS
// =========================================================
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // -----------------------------------------------
    // Acceso general (cualquier usuario autenticado)
    // -----------------------------------------------
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    Route::get('/cuentapersonal', fn() => view('paginas.cuentapersonal'))
        ->name('cuentapersonal');

    Route::get('/registro_de_personaje', fn() => view('paginas.personajesregistro'))
        ->name('personajesregistro');

    // -----------------------------------------------
    // Módulos del gremio (Ver Linhir)
    // -----------------------------------------------
    Route::middleware('can:Ver Linhir')->group(function () {
        Route::get('/linhir', fn() => view('paginas.linhir-listado'))->name('linhir');
        Route::get('/ranking', fn() => view('paginas.ranking'))->name('ranking');
    });

    // -----------------------------------------------
    // Batallas
    // -----------------------------------------------
    Route::middleware('can:Ver Batallas')->group(function () {

        // Análisis de Guerra
        Route::get('/battles/war-analysis', fn() => view('paginas.war-analysis'))
            ->name('battles.war-analysis');

        Route::get('/battles/multi', function () {
            return view('paginas.battle-multi', [
                'ids' => (string) request()->query('ids', ''),
            ]);
        })->name('battles.multi');

        Route::get('/battles', fn() => view('paginas.battles'))->name('battles.index');

        Route::get('/battles/{id}', fn($id) => view('paginas.battle-detail', ['id' => $id]))
            ->name('battles.show');
    });

    // -----------------------------------------------
    // Discord
    // -----------------------------------------------
    Route::middleware('can:Ver Discord')->group(function () {
        Route::get('/admin/discord', fn() => view('paginas.discord'))->name('discord.index');
    });

    // -----------------------------------------------
    // Usuarios
    // -----------------------------------------------
    Route::middleware('can:Ver Usuarios')->group(function () {
        Route::get('/usuarios', fn() => view('paginas.usuarios'))->name('usuarios');
    });

    // -----------------------------------------------
    // Tareas
    // -----------------------------------------------
    Route::middleware('can:manage_tasks')->group(function () {
        Route::get('/admin/tasks', fn() => view('paginas.tasks-management'))->name('tasks.management');
    });

    // -----------------------------------------------
    // Roles y Permisos
    // -----------------------------------------------
    Route::middleware('can:Ver Roles y Permisos')->group(function () {
        Route::get('/roles', fn() => view('paginas.rolesypermisos'))->name('roles');
    });

    // -----------------------------------------------
    // CTAs
    // -----------------------------------------------
    // ⚠️ IMPORTANTE: ctas.create DEBE ir antes de ctas.show
    // para que /ctas/crear no se confunda con /ctas/{id}
    Route::middleware('can:Gestionar CTAs')->group(function () {
        Route::get('/ctas/crear', fn() => view('paginas.crear-cta'))
            ->name('ctas.create');

        Route::get('/admin/composiciones', fn() => view('paginas.composiciones'))
            ->name('composiciones.index');
    });

    Route::middleware('can:Ver CTAs')->group(function () {
        Route::get('/ctas', fn() => view('paginas.ctas-index'))->name('ctas.index');
        Route::get('/ctas/{id}', fn($id) => view('paginas.cta-detail', ['id' => $id]))
            ->name('ctas.show');
    });

    // -----------------------------------------------
    // Actividades
    // -----------------------------------------------
    Route::middleware('can:Ver Actividades')->group(function () {
        Route::get('/actividades', fn() => view('paginas.actividades-index'))
            ->name('actividades.index');
    });

    // -----------------------------------------------
    // Cuenta gremial
    // -----------------------------------------------
    Route::middleware('can:Ver Cuenta Gremial')->group(function () {
        Route::get('/cuenta-gremial', fn() => view('paginas.cuenta-gremial'))
            ->name('cuenta-gremial.index');
    });

    // -----------------------------------------------
    // Solicitudes de retiro
    // -----------------------------------------------
    Route::middleware('can:Gestionar Cuenta Gremial')->group(function () {
        Route::get('/admin/retiros', fn() => view('paginas.retiros-pendientes'))
            ->name('retiros.admin');
    });

    // -----------------------------------------------
    // Dashboard Administrativo (NUEVO)
    // -----------------------------------------------
    Route::middleware('can:Ver Usuarios')->group(function () {
        Route::get('/admin/dashboard', fn() => view('paginas.admin-dashboard'))
            ->name('admin.dashboard');
    });
});
