<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

// Redirección a proveedor (discord)
Route::get('/auth/discord', [UserController::class, 'redirect_api']);
Route::get('/auth/discord/callback', [UserController::class, 'callback_api']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/logout', [UserController::class, 'logout']);
});
