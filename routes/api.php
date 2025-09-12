<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ApisController;

// Redirección a proveedor (discord)
Route::get('/auth/discord', [UserController::class, 'redirect_api']);
Route::get('/auth/discord/callback', [UserController::class, 'callback_api']);

Route::get('/oro', [ApisController::class, 'valordeloro'])->middleware('auth:sanctum');

Route::get('/nombre/{nombredelpersonaje}', [ApisController::class, 'nombredepersonaje'])->middleware('auth:sanctum');
Route::post('/vincular-personaje-discord', [ApisController::class, 'vincularPersonajeDiscord'])->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/logout', [UserController::class, 'logout']);
});
