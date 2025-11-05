<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialiteAuthController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/hrl', function () {
    return view('hrlali');
})->name('hrl');

Route::get('/auth/redirect', [SocialiteAuthController::Class, 'redirect'])->name('auth.redirect');
Route::get('/auth/callback', [SocialiteAuthController::Class, 'callback'])->name('auth.callback');

// Agrega esta ruta para poder verificar el sitemap fácilmente
Route::get('/sitemap.xml', function () {
    return response()->file(public_path('sitemap.xml'));
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/linhir', function () {
        return view('paginas.linhir-listado');
    })->name('linhir');

    Route::get('/usuarios', function () {
        return view('paginas.usuarios');
    })->name('usuarios');

    Route::get('/roles', function () {
        return view('paginas.rolesypermisos');
    })->name('roles');

    Route::get('/bancogremial', function () {
        return view('paginas.bancodegremio');
    })->name('bancodegremio');

    Route::get('/registro_de_personaje', function () {
        return view('paginas.personajesregistro');
    })->name('personajesregistro');
});
