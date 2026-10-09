<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ApisController;
use App\Http\Controllers\Api\OllamaController;
use App\Http\Controllers\Api\Bot\TaskController;
use App\Http\Controllers\Api\Bot\ReportController;
use App\Http\Controllers\Api\Bot\PointController;
use App\Http\Controllers\Api\Bot\IdentityController;

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


/*
|--------------------------------------------------------------------------
| BOT LINHIR — API dedicada para bots oficiales
|--------------------------------------------------------------------------
| Autenticación: Sanctum Bearer token (cuenta bot existente).
| Consumida por: Linhir Assistant Bot y futuros bots del sistema.
|
| - /api/bot/* se reserva para comandos de bots.
| - Requiere ability "read" para endpoints de consulta.
| - Añadir "ability:create" / "ability:update" / "ability:delete"
|   conforme se implementen comandos de mutación (bot de tareas).
|--------------------------------------------------------------------------
*/

Route::prefix('bot')->middleware(['auth:sanctum', 'ability:read'])->group(function () {
    Route::get('/horario', [ApisController::class, 'horario']);
    Route::get('/oro',     [ApisController::class, 'valordeloro']);
    Route::post('/register-character', [IdentityController::class, 'registerCharacter']);
    Route::post('/sync-registration',  [IdentityController::class, 'syncRegistration']);
    Route::get('/members', [IdentityController::class, 'listMembers']);
});


//prueba api IA
Route::get('/ollama', [OllamaController::class, 'index'])->middleware('auth:sanctum');

// Rutas para el bot de Discord de Linhir

Route::prefix('bot')->middleware('auth:sanctum')->group(function () {
    // Tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    Route::patch('/tasks/{id}/activate', [TaskController::class, 'activate']);

    // Reports
    Route::get('/reports/pending', [ReportController::class, 'pending']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports/{id}', [ReportController::class, 'show']);
    Route::put('/reports/{id}/approve', [ReportController::class, 'approve']);
    Route::put('/reports/{id}/reject', [ReportController::class, 'reject']);

    // Points
    Route::get('/ranking', [PointController::class, 'ranking']);
    Route::get('/points/all', [PointController::class, 'allUsers']);
    Route::get('/points/{discord_user_id}', [PointController::class, 'show']);
    Route::get('/points/history/{discord_user_id}', [PointController::class, 'history']);
    Route::post('/points/add', [PointController::class, 'add']);
    Route::post('/points/spend', [PointController::class, 'spend']);
});
