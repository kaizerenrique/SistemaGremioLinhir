<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialiteAuthController;
use App\Livewire\Blog\BlogIndex;
use App\Livewire\Blog\BlogShow;

// =========================================================
// RUTAS PÚBLICAS
// =========================================================
Route::get('/', fn () => view('welcome'))->name('welcome');

Route::get('/calculadora-de-cultivos', fn () => view('calculadoradesemillas'))
    ->name('calculadoradesemillas');

Route::get('/auth/redirect', [SocialiteAuthController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/callback', [SocialiteAuthController::class, 'callback'])->name('auth.callback');

Route::get('/sitemap.xml', fn () => response()->file(public_path('sitemap.xml')));

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
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::get('/cuentapersonal', fn () => view('paginas.cuentapersonal'))
        ->name('cuentapersonal');

    Route::get('/registro_de_personaje', fn () => view('paginas.personajesregistro'))
        ->name('personajesregistro');

    // -----------------------------------------------
    // Módulos del gremio (Ver Linhir)
    // -----------------------------------------------
    Route::middleware('can:Ver Linhir')->group(function () {
        Route::get('/linhir', fn () => view('paginas.linhir-listado'))->name('linhir');
        Route::get('/ranking', fn () => view('paginas.ranking'))->name('ranking');
    });

    // -----------------------------------------------
    // Batallas
    // -----------------------------------------------
    Route::middleware('can:Ver Batallas')->group(function () {
        Route::get('/battles', fn () => view('paginas.battles'))->name('battles.index');

        Route::get('/battles/{id}', fn ($id) => view('paginas.battle-detail', ['id' => $id]))
            ->name('battles.show');
    });

    // -----------------------------------------------
    // Discord
    // -----------------------------------------------
    Route::middleware('can:Ver Discord')->group(function () {
        Route::get('/admin/discord', fn () => view('paginas.discord'))->name('discord.index');
    });

    // -----------------------------------------------
    // Banco Gremial
    // -----------------------------------------------
    Route::middleware('can:Ver Banco Gremial')->group(function () {
        Route::get('/bancogremial', fn () => view('paginas.bancodegremio'))->name('bancodegremio');
    });

    // -----------------------------------------------
    // Usuarios
    // -----------------------------------------------
    Route::middleware('can:Ver Usuarios')->group(function () {
        Route::get('/usuarios', fn () => view('paginas.usuarios'))->name('usuarios');
    });

    // -----------------------------------------------
    // Tareas
    // -----------------------------------------------
    Route::middleware('can:manage_tasks')->group(function () {
        Route::get('/admin/tasks', fn () => view('paginas.tasks-management'))->name('tasks.management');
    });

    // -----------------------------------------------
    // Roles y Permisos
    // -----------------------------------------------
    Route::middleware('can:Ver Roles y Permisos')->group(function () {
        Route::get('/roles', fn () => view('paginas.rolesypermisos'))->name('roles');
    });
});
