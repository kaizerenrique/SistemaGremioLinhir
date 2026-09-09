<?php

namespace App\Services\Tasks;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    /**
     * Obtener todas las tareas activas.
     */
    public function listActive(): Collection
    {
        return Task::where('active', true)->orderBy('id', 'desc')->get();
    }

    /**
     * Obtener todas las tareas (incluyendo inactivas).
     */
    public function listAll(): Collection
    {
        return Task::orderBy('id', 'desc')->get();
    }

    /**
     * Crear una nueva tarea.
     */
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Actualizar una tarea existente (solo campos proporcionados).
     */
    public function update(int $id, array $data): Task
    {
        $task = Task::findOrFail($id);
        $task->update($data);
        return $task;
    }

    /**
     * Desactivar una tarea (soft delete lógico).
     */
    public function deactivate(int $id): Task
    {
        $task = Task::findOrFail($id);
        $task->update(['active' => false]);
        return $task;
    }

    /**
     * Activar una tarea (reactivar).
     */
    public function activate(int $id): Task
    {
        $task = Task::findOrFail($id);
        $task->update(['active' => true]);
        return $task;
    }

    /**
     * Obtener una tarea por ID.
     */
    public function find(int $id): Task
    {
        return Task::findOrFail($id);
    }
}