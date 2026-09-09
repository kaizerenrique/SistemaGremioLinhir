<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tasks\TaskService;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Listar tareas activas.
     */
    public function index()
    {
        $tasks = $this->taskService->listActive();
        return response()->json($tasks);
    }

    /**
     * Crear una nueva tarea.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'points' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'repeatable' => 'nullable|boolean',
        ]);

        $task = $this->taskService->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tarea creada exitosamente.',
            'task' => $task,
        ], 201);
    }

    /**
     * Actualizar una tarea.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'points' => 'sometimes|integer|min:1',
            'description' => 'nullable|string',
            'repeatable' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);

        $task = $this->taskService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Tarea actualizada correctamente.',
            'task' => $task,
        ]);
    }

    /**
     * Desactivar una tarea (soft delete).
     */
    public function destroy(int $id)
    {
        $this->taskService->deactivate($id);

        return response()->json([
            'success' => true,
            'message' => 'Tarea desactivada correctamente.',
        ]);
    }

    /**
     * Activar una tarea (reactivar).
     */
    public function activate(int $id)
    {
        $task = $this->taskService->activate($id);

        return response()->json([
            'success' => true,
            'message' => 'Tarea reactivada correctamente.',
            'task' => $task,
        ]);
    }

    /**
     * Obtener una tarea específica.
     */
    public function show(int $id)
    {
        $task = $this->taskService->find($id);
        return response()->json($task);
    }
}
