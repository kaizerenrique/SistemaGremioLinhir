<?php

namespace App\Livewire\Modulo\Admin;

use Livewire\Component;
use App\Models\Task;
use App\Services\Tasks\TaskService;
use Livewire\WithPagination;

class TasksManagement extends Component
{
    use WithPagination;

    protected $taskService;
    protected $listeners = ['taskUpdated' => '$refresh'];

    // Propiedades para el modal
    public $showModal = false;
    public $editing = false;
    public $taskId = null;
    public $name = '';
    public $points = '';
    public $description = '';
    public $repeatable = false;
    public $active = true;

    // Propiedades para el modal de confirmación
    public $confirmingDeletion = false;
    public $taskToDeleteId = null;
    public $taskToDeleteName = '';
    public $deleteAction = 'deactivate'; // 'deactivate' o 'activate'

    // Reglas de validación
    protected $rules = [
        'name' => 'required|string|max:100',
        'points' => 'required|integer|min:1',
        'description' => 'nullable|string|max:1000',
        'repeatable' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'El nombre de la tarea es obligatorio.',
        'points.required' => 'Los puntos son obligatorios.',
        'points.integer' => 'Los puntos deben ser un número entero.',
        'points.min' => 'Los puntos deben ser al menos 1.',
    ];

    public function boot(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }   

    public function render()
    {
        $tasks = Task::orderBy('id', 'desc')->paginate(10);
        return view('livewire.modulo.admin.tasks-management', [
            'tasks' => $tasks,
        ]);
    }

    // Abrir modal para crear nueva tarea
    public function createTask()
    {
        $this->resetForm();
        $this->editing = false;
        $this->showModal = true;
        $this->dispatch('open-modal');
    }

    // Abrir modal para editar tarea (activa o inactiva)
    public function editTask($id)
    {
        $task = Task::findOrFail($id);
        $this->taskId = $task->id;
        $this->name = $task->name;
        $this->points = (string) $task->points;
        $this->description = $task->description;
        $this->repeatable = (bool) $task->repeatable;
        $this->active = (bool) $task->active;
        $this->editing = true;
        $this->showModal = true;
        $this->dispatch('open-modal');
    }

    // Guardar tarea (crear o actualizar)
    public function saveTask()
    {
        $this->validate();

        try {
            if ($this->editing) {
                // Actualizar tarea (mantiene el estado active actual)
                $this->taskService->update($this->taskId, [
                    'name' => $this->name,
                    'points' => (int) $this->points,
                    'description' => $this->description,
                    'repeatable' => $this->repeatable,
                    // No actualizamos 'active' aquí para no sobreescribir
                ]);
                $message = 'Tarea actualizada correctamente.';
            } else {
                $this->taskService->create([
                    'name' => $this->name,
                    'points' => (int) $this->points,
                    'description' => $this->description,
                    'repeatable' => $this->repeatable,
                ]);
                $message = 'Tarea creada correctamente.';
            }

            $this->resetForm();
            $this->showModal = false;
            $this->dispatch('close-modal');
            $this->dispatch('notify', ['message' => $message, 'type' => 'success']);
            $this->dispatch('taskUpdated');

        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Error: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    // Confirmar acción (activar o desactivar)
    public function confirmAction($id, $action)
    {
        $task = Task::findOrFail($id);
        $this->taskToDeleteId = $task->id;
        $this->taskToDeleteName = $task->name;
        $this->deleteAction = $action; // 'activate' o 'deactivate'
        $this->confirmingDeletion = true;
    }

    // Ejecutar la acción según deleteAction
    public function executeAction()
    {
        try {
            if ($this->deleteAction === 'activate') {
                $this->taskService->activate($this->taskToDeleteId);
                $message = 'Tarea activada correctamente.';
            } else {
                $this->taskService->deactivate($this->taskToDeleteId);
                $message = 'Tarea desactivada correctamente.';
            }

            $this->confirmingDeletion = false;
            $this->taskToDeleteId = null;
            $this->taskToDeleteName = '';
            $this->deleteAction = 'deactivate';
            $this->dispatch('notify', ['message' => $message, 'type' => 'success']);
            $this->dispatch('taskUpdated');
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => 'Error: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    // Cancelar y cerrar modales
    public function cancel()
    {
        $this->resetForm();
        $this->showModal = false;
        $this->confirmingDeletion = false;
        $this->taskToDeleteId = null;
        $this->taskToDeleteName = '';
        $this->deleteAction = 'deactivate';
        $this->dispatch('close-modal');
    }

    private function resetForm()
    {
        $this->taskId = null;
        $this->name = '';
        $this->points = '';
        $this->description = '';
        $this->repeatable = false;
        $this->active = true;
        $this->editing = false;
        $this->resetValidation();
    }
}
